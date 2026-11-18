<?php

namespace App\Livewire\Ventas;

use App\Models\Almacen;
use App\Models\CodigoIdentificacion;
use App\Models\DetalleVenta;
use App\Models\Existencia;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\UnidadInventario;
use App\Models\User;
use App\Models\Venta;
use App\Services\PagoVentaService;
use App\Services\VentaService;
use App\Services\AuditoriaService;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Ventas | MULTIVERSE')]
class Index extends Component
{
    use WithPagination;

    #[Locked]
    public int $establecimiento;

    #[Locked]
    public int $almacen;

    public array $lineas = [['producto_id' => '', 'cantidad' => '1', 'lote_id' => '', 'unidad_inventario_id' => '']];

    public string $busquedaProducto = '';

    public string $codigoEscaneado = '';

    public string $tipoCodigo = 'barcode';

    public int $productoCodigoId = 0;

    public int $ventaPagoId = 0;

    public string $montoPago = '';

    public string $metodoPago = 'efectivo';

    public string $referenciaPago = '';

    public string $importeRecibido = '';

    public ?array $ventaCompletada = null;

    public bool $mostrarOperaciones = false;

    public bool $mostrarAutorizacionRetiro = false;

    #[Locked]
    public int $lineaRetiroIndice = -1;

    #[Locked]
    public int $productoRetiroId = 0;

    #[Locked]
    public float $cantidadRetiro = 0;

    public string $correoGerente = '';

    public string $contrasenaGerente = '';

    public string $motivoRetiroLinea = '';

    public function mount(int $establecimiento, int $almacen): void
    {
        $this->establecimiento = $establecimiento;
        $this->almacen = $almacen;
    }

    public function agregarLinea(): void
    {
        $this->lineas[] = ['producto_id' => '', 'cantidad' => '1', 'lote_id' => '', 'unidad_inventario_id' => ''];
    }

    public function agregarProducto(int $productoId): void
    {
        $this->autorizarVentas();
        $this->ventaCompletada = null;
        $producto = Producto::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('activo', true)
            ->findOrFail($productoId);

        $esVentas = auth()->user()->roles()->where('roles.slug', 'ventas')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists();
        if ($esVentas && ! Existencia::query()->where('tenant_id', auth()->user()->tenant_id)
            ->where('almacen_id', $this->almacen)->where('producto_id', $productoId)->where('cantidad', '>', 0)->exists()) {
            throw ValidationException::withMessages(['codigoEscaneado' => 'Este producto no tiene existencias disponibles en tu punto de venta.']);
        }

        if (! $producto->requiere_serie) {
            foreach ($this->lineas as $indice => $linea) {
                if ((int) ($linea['producto_id'] ?? 0) === $productoId && blank($linea['unidad_inventario_id'] ?? null)) {
                    $this->lineas[$indice]['cantidad'] = (string) ((float) ($linea['cantidad'] ?: 0) + 1);
                    return;
                }
            }
        }

        if (count($this->lineas) === 1 && blank($this->lineas[0]['producto_id'])) {
            $this->lineas[0]['producto_id'] = (string) $productoId;
            return;
        }

        $this->lineas[] = ['producto_id' => (string) $productoId, 'cantidad' => '1', 'lote_id' => '', 'unidad_inventario_id' => ''];
    }

    public function agregarProductoDesdeBusqueda(int $productoId): void
    {
        $this->agregarProducto($productoId);
        $this->reset('busquedaProducto');
    }

    public function buscarCodigo(): void
    {
        $this->autorizarVentas();
        $datos = $this->validate(['codigoEscaneado' => ['required', 'string', 'max:255']]);
        $tenantId = (int) auth()->user()->tenant_id;
        $codigo = trim($datos['codigoEscaneado']);
        $producto = Producto::query()->where('tenant_id', $tenantId)->where('activo', true)
            ->where(fn ($query) => $query->where('sku', $codigo)
                ->orWhereHas('codigosIdentificacion', fn ($codes) => $codes->where('tenant_id', $tenantId)->where('codigo', $codigo)))
            ->first();

        if ($producto === null) {
            $esVentas = auth()->user()->roles()->where('roles.slug', 'ventas')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists();
            $this->addError('codigoEscaneado', $esVentas
                ? 'Este código todavía no está asociado. Pide al administrador que registre el producto.'
                : 'Código sin asociar. Selecciona el producto y guarda este código para agregarlo.');
            $this->dispatch('barcode-ready');
            return;
        }

        $this->agregarProducto((int) $producto->getKey());
        $this->reset('codigoEscaneado');
        $this->resetErrorBag('codigoEscaneado');
        $this->dispatch('barcode-ready');
        Flux::toast(variant: 'success', text: "Agregado al carrito: {$producto->nombre}.");
    }

    public function guardarCodigoYAgregar(): void
    {
        $this->autorizarVentas();
        abort_unless(auth()->user()->roles()->whereIn('roles.slug', ['admin', 'gerente'])->wherePivot('tenant_id', auth()->user()->tenant_id)->exists(), 403);
        $tenantId = (int) auth()->user()->tenant_id;
        $datos = $this->validate([
            'productoCodigoId' => ['required', 'integer', 'min:1'],
            'codigoEscaneado' => ['required', 'string', 'max:255'],
            'tipoCodigo' => ['required', 'in:barcode,qr'],
        ]);
        $producto = Producto::query()->where('tenant_id', $tenantId)->where('activo', true)->findOrFail($datos['productoCodigoId']);
        $codigo = trim($datos['codigoEscaneado']);

        if (CodigoIdentificacion::query()->where('tenant_id', $tenantId)->where('codigo', $codigo)->exists()) {
            $this->addError('codigoEscaneado', 'Ese código ya está asignado a un producto.');
            return;
        }

        CodigoIdentificacion::query()->create([
            'tenant_id' => $tenantId,
            'producto_id' => $producto->getKey(),
            'tipo' => $datos['tipoCodigo'],
            'codigo' => $codigo,
        ]);

        $this->agregarProducto((int) $producto->getKey());
        $this->reset('codigoEscaneado');
        $this->resetErrorBag('codigoEscaneado');
        $this->dispatch('barcode-ready');
        Flux::toast(variant: 'success', text: 'Código guardado y producto agregado al carrito.');
    }

    public function prepararQuitarLinea(int $indice): void
    {
        $this->autorizarVentas();

        if (! array_key_exists($indice, $this->lineas)) {
            return;
        }

        if (! $this->esEncargadoVentas()) {
            $this->eliminarLineaCarrito($indice);
            return;
        }

        $this->resetValidation(['correoGerente', 'contrasenaGerente', 'motivoRetiroLinea']);
        $this->lineaRetiroIndice = $indice;
        $this->productoRetiroId = (int) ($this->lineas[$indice]['producto_id'] ?? 0);
        $this->cantidadRetiro = (float) ($this->lineas[$indice]['cantidad'] ?? 0);
        $this->mostrarAutorizacionRetiro = true;
    }

    public function autorizarYQuitarLinea(AuditoriaService $auditoria): void
    {
        $correo = mb_strtolower(trim($this->correoGerente));
        $contrasena = $this->contrasenaGerente;
        $motivo = trim($this->motivoRetiroLinea);
        // Nunca devolver la contraseña del gerente dentro del snapshot de Livewire.
        $this->reset('contrasenaGerente');

        $this->autorizarVentas();
        abort_unless($this->esEncargadoVentas(), 403);

        if ($this->lineaRetiroIndice < 0 || ! array_key_exists($this->lineaRetiroIndice, $this->lineas)) {
            $this->addError('lineaRetiroIndice', 'La línea del carrito ya no está disponible.');
            return;
        }

        if ($correo === '' || $contrasena === '') {
            $this->addError('contrasenaGerente', 'Ingresa el correo y la contraseña del gerente o administrador.');
            return;
        }

        if (mb_strlen($motivo) < 5) {
            $this->addError('motivoRetiroLinea', 'Describe brevemente por qué se debe quitar el producto.');
            return;
        }

        $limiterKey = 'pos-manager-override:'.auth()->id().':'.$this->establecimiento;
        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $this->addError('contrasenaGerente', 'Se hicieron varios intentos. Espera un minuto e inténtalo de nuevo.');
            return;
        }

        $tenantId = (int) auth()->user()->tenant_id;
        $gerente = User::query()->where('tenant_id', $tenantId)->where('activo', true)
            ->whereRaw('LOWER(email) = ?', [$correo])->first();
        $rolesGerencia = $gerente?->roles()->whereIn('roles.slug', ['admin', 'gerente'])->wherePivot('tenant_id', $tenantId)->get() ?? collect();
        $esAdminTenant = $rolesGerencia->contains('slug', 'admin');
        $esGerenteAsignado = $rolesGerencia->contains('slug', 'gerente') && DB::table('usuario_establecimiento')
            ->where('tenant_id', $tenantId)->where('usuario_id', $gerente?->getKey())
            ->where('establecimiento_id', $this->establecimiento)->exists();

        if ($gerente === null || (! $esAdminTenant && ! $esGerenteAsignado) || ! Hash::check($contrasena, $gerente->password)) {
            RateLimiter::hit($limiterKey, 60);
            $this->addError('contrasenaGerente', 'Las credenciales no corresponden a un gerente o administrador asignado a esta sucursal.');
            return;
        }

        RateLimiter::clear($limiterKey);
        $indice = $this->lineaRetiroIndice;
        $linea = $this->lineas[$indice];
        $producto = Producto::query()->where('tenant_id', $tenantId)->find($this->productoRetiroId);
        if ($producto === null || (int) ($linea['producto_id'] ?? 0) !== $this->productoRetiroId || (float) ($linea['cantidad'] ?? 0) !== $this->cantidadRetiro) {
            $this->addError('lineaRetiroIndice', 'El producto ya no está disponible.');
            return;
        }

        $auditoria->registrar(
            (int) auth()->id(),
            'producto_quitado_caja_autorizado',
            'CarritoVenta',
            null,
            $tenantId,
            null,
            [
                'producto_id' => (int) $producto->getKey(),
                'sku' => $producto->sku,
                'nombre' => $producto->nombre,
                'cantidad' => $this->cantidadRetiro,
                'gerente_autorizador_id' => (int) $gerente->getKey(),
                'motivo' => $motivo,
            ],
            $this->establecimiento,
        );

        $this->eliminarLineaCarrito($indice);
        $this->reset('correoGerente', 'contrasenaGerente', 'motivoRetiroLinea', 'lineaRetiroIndice', 'productoRetiroId', 'cantidadRetiro', 'mostrarAutorizacionRetiro');
        Flux::toast(variant: 'success', text: 'Producto quitado con autorización de gerencia.');
    }

    public function cancelarAutorizacionRetiro(): void
    {
        $this->reset('correoGerente', 'contrasenaGerente', 'motivoRetiroLinea', 'lineaRetiroIndice', 'productoRetiroId', 'cantidadRetiro', 'mostrarAutorizacionRetiro');
        $this->resetValidation(['correoGerente', 'contrasenaGerente', 'motivoRetiroLinea', 'lineaRetiroIndice']);
    }

    private function eliminarLineaCarrito(int $indice): void
    {
        unset($this->lineas[$indice]);
        $this->lineas = array_values($this->lineas);

        if ($this->lineas === []) {
            $this->lineas = [['producto_id' => '', 'cantidad' => '1', 'lote_id' => '', 'unidad_inventario_id' => '']];
        }
    }

    public function registrarVenta(): void
    {
        $this->autorizarVentas();
        $datos = $this->validate([
            'lineas' => ['required', 'array', 'min:1'],
            'lineas.*.producto_id' => ['required', 'integer', 'min:1'],
            'lineas.*.cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,3'],
            'lineas.*.lote_id' => ['nullable', 'integer'],
            'lineas.*.unidad_inventario_id' => ['nullable', 'integer'],
        ]);
        $tenantId = (int) auth()->user()->tenant_id;
        $this->crearBorrador($datos['lineas'], $tenantId);

        $this->reset('lineas');
        $this->lineas = [['producto_id' => '', 'cantidad' => '1', 'lote_id' => '', 'unidad_inventario_id' => '']];
        $this->ventaCompletada = null;
        $this->dispatch('barcode-ready');
        Flux::toast(variant: 'success', text: 'Venta en borrador creada. Confirma para descontar existencias.');
    }

    public function cobrarVenta(VentaService $ventaService, PagoVentaService $pagoService): void
    {
        $this->autorizarVentas();
        $datos = $this->validate([
            'lineas' => ['required', 'array', 'min:1'],
            'lineas.*.producto_id' => ['required', 'integer', 'min:1'],
            'lineas.*.cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,3'],
            'lineas.*.lote_id' => ['nullable', 'integer'],
            'lineas.*.unidad_inventario_id' => ['nullable', 'integer'],
            'metodoPago' => ['required', 'in:efectivo,tarjeta,transferencia,otro'],
            'importeRecibido' => ['required_if:metodoPago,efectivo', 'nullable', 'numeric', 'gt:0', 'decimal:0,2'],
            'referenciaPago' => ['nullable', 'string', 'max:255'],
        ]);
        $tenantId = (int) auth()->user()->tenant_id;

        $venta = DB::transaction(function () use ($datos, $tenantId, $ventaService, $pagoService): Venta {
            $venta = $this->crearBorrador($datos['lineas'], $tenantId);
            $total = (float) $venta->total;
            $recibido = $datos['metodoPago'] === 'efectivo' ? (float) $datos['importeRecibido'] : $total;

            if ($recibido + 0.0001 < $total) {
                throw ValidationException::withMessages(['importeRecibido' => 'El importe recibido debe cubrir el total de la venta.']);
            }

            $venta = $ventaService->confirmar($venta);
            $pagoService->registrar(
                (int) $venta->getKey(),
                number_format($total, 2, '.', ''),
                $datos['metodoPago'],
                filled($datos['referenciaPago'] ?? null) ? trim($datos['referenciaPago']) : null,
            );

            return $venta->fresh(['detallesVenta.producto', 'pagos']);
        });

        $total = (float) $venta->total;
        $recibido = $datos['metodoPago'] === 'efectivo' ? (float) $datos['importeRecibido'] : $total;
        $this->ventaCompletada = [
            'folio' => $venta->folio,
            'total' => number_format($total, 2),
            'cambio' => number_format(max(0, $recibido - $total), 2),
        ];
        $this->reset('lineas', 'importeRecibido', 'referenciaPago');
        $this->lineas = [['producto_id' => '', 'cantidad' => '1', 'lote_id' => '', 'unidad_inventario_id' => '']];
        $this->resetPage();
        $this->dispatch('barcode-ready');
        Flux::toast(variant: 'success', text: 'Venta cobrada; existencias y pago registrados.');
    }

    /** @param array<int, array<string, mixed>> $lineas */
    private function crearBorrador(array $lineas, int $tenantId): Venta
    {
        return DB::transaction(function () use ($lineas, $tenantId): Venta {
            $venta = Venta::query()->create([
                'tenant_id' => $tenantId,
                'establecimiento_id' => $this->establecimiento,
                'almacen_id' => $this->almacen,
                'usuario_id' => (int) auth()->id(),
                'folio' => 'V-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'estado' => 'borrador',
                'subtotal' => 0,
                'impuestos' => 0,
                'descuento' => 0,
                'total' => 0,
            ]);

            $subtotal = 0;
            foreach ($lineas as $indice => $linea) {
                $producto = Producto::query()->where('tenant_id', $tenantId)->where('activo', true)->findOrFail($linea['producto_id']);
                $cantidad = (float) $linea['cantidad'];
                $loteId = filled($linea['lote_id'] ?? null) ? (int) $linea['lote_id'] : null;
                $unidadId = filled($linea['unidad_inventario_id'] ?? null) ? (int) $linea['unidad_inventario_id'] : null;

                if ($loteId !== null && ! Lote::query()->where('tenant_id', $tenantId)->where('producto_id', $producto->getKey())->whereKey($loteId)->exists()) {
                    throw ValidationException::withMessages(["lineas.{$indice}.lote_id" => 'El lote seleccionado no existe o no pertenece a este producto.']);
                }
                if ($producto->requiere_serie && $unidadId === null) {
                    throw ValidationException::withMessages(['lineas' => 'Selecciona la unidad serializada de cada producto que lo requiera.']);
                }
                if ($unidadId !== null) {
                    $unidad = UnidadInventario::query()->where('tenant_id', $tenantId)->where('producto_id', $producto->getKey())->where('estado', 'disponible')->findOrFail($unidadId);
                    if ((int) $unidad->ubicacion?->almacen_id !== $this->almacen || $cantidad !== 1.0) {
                        throw ValidationException::withMessages(['lineas' => 'La unidad debe estar disponible en este almacén y su cantidad debe ser uno.']);
                    }
                }

                $precio = (float) $producto->precio_venta;
                $importe = round($precio * $cantidad, 2);
                DetalleVenta::query()->create([
                    'tenant_id' => $tenantId,
                    'venta_id' => $venta->getKey(),
                    'producto_id' => $producto->getKey(),
                    'unidad_inventario_id' => $unidadId,
                    'lote_id' => $loteId,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'descuento' => 0,
                    'impuesto' => 0,
                    'total' => $importe,
                ]);
                $subtotal += $importe;
            }

            $venta->update(['subtotal' => $subtotal, 'total' => $subtotal]);

            return $venta;
        });
    }

    public function confirmar(int $ventaId, VentaService $service): void
    {
        $this->autorizarGestionVentas();
        $venta = Venta::query()->where('tenant_id', auth()->user()->tenant_id)->where('establecimiento_id', $this->establecimiento)->where('almacen_id', $this->almacen)->findOrFail($ventaId);
        $service->confirmar($venta);
        $this->dispatch('barcode-ready');
        Flux::toast(variant: 'success', text: 'Venta confirmada y existencias actualizadas.');
    }

    public function registrarPago(PagoVentaService $service): void
    {
        $this->autorizarGestionVentas();
        $datos = $this->validate([
            'ventaPagoId' => ['required', 'integer', 'min:1'],
            'montoPago' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'metodoPago' => ['required', 'in:efectivo,tarjeta,transferencia,otro'],
            'referenciaPago' => ['nullable', 'string', 'max:255'],
        ]);
        Venta::query()->where('tenant_id', auth()->user()->tenant_id)
            ->where('establecimiento_id', $this->establecimiento)->where('almacen_id', $this->almacen)
            ->findOrFail($datos['ventaPagoId']);
        $service->registrar($datos['ventaPagoId'], $datos['montoPago'], $datos['metodoPago'], $datos['referenciaPago'] ?: null);
        $this->reset('ventaPagoId', 'montoPago', 'referenciaPago');
        $this->dispatch('barcode-ready');
        Flux::toast(variant: 'success', text: 'Pago registrado.');
    }

    private function esEncargadoVentas(): bool
    {
        return auth()->user()->roles()->where('roles.slug', 'ventas')->wherePivot('tenant_id', auth()->user()->tenant_id)->exists();
    }

    private function autorizarGestionVentas(): void
    {
        $this->autorizarVentas();

        $puedeGestionar = auth()->user()->roles()
            ->whereIn('roles.slug', ['admin', 'gerente'])
            ->wherePivot('tenant_id', auth()->user()->tenant_id)
            ->exists();

        abort_unless($puedeGestionar, 403);
    }

    private function autorizarVentas(): void
    {
        $user = auth()->user();
        $tenantId = (int) $user->tenant_id;
        $hasRole = $user->roles()
            ->whereIn('roles.slug', ['admin', 'gerente', 'ventas'])
            ->wherePivot('tenant_id', $tenantId)
            ->exists();
        $warehouse = Almacen::query()->where('tenant_id', $tenantId)->where('establecimiento_id', $this->establecimiento)
            ->where('activo', true)->whereHas('establecimiento', fn ($query) => $query->where('activo', true))->find($this->almacen);
        $admin = $user->roles()->where('roles.slug', 'admin')->wherePivot('tenant_id', $tenantId)->exists();
        $sales = $user->roles()->where('roles.slug', 'ventas')->wherePivot('tenant_id', $tenantId)->exists();
        $manager = $user->roles()->where('roles.slug', 'gerente')->wherePivot('tenant_id', $tenantId)->exists();
        $assigned = DB::table('usuario_establecimiento')->where('tenant_id', $tenantId)->where('usuario_id', $user->getKey())
            ->where('establecimiento_id', $this->establecimiento)->exists();
        $assignedWarehouse = DB::table('usuario_almacen')->where('tenant_id', $tenantId)->where('usuario_id', $user->getKey())
            ->where('almacen_id', $this->almacen)->exists();

        abort_unless($hasRole && $warehouse !== null && (! $manager || $user->puede_ver_punto_venta) && ($admin || ($sales ? $assignedWarehouse : $assigned)), 403);
    }

    public function render()
    {
        $tenantId = (int) auth()->user()->tenant_id;
        $ventas = Venta::query()->where('tenant_id', $tenantId)
            ->where('establecimiento_id', $this->establecimiento)->where('almacen_id', $this->almacen)
            ->with(['detallesVenta.producto', 'pagos', 'usuario:id,name'])->latest('id')->paginate(20);
        $productosEnCarrito = collect($this->lineas)->pluck('producto_id')->filter()->map(fn ($id) => (int) $id)->all();
        $esVendedor = auth()->user()->roles()->where('roles.slug', 'ventas')->wherePivot('tenant_id', $tenantId)->exists();
        $productos = Producto::query()->where('tenant_id', $tenantId)->where('activo', true)
            ->withSum(['existencias as stock_disponible' => fn ($query) => $query
                ->where('tenant_id', $tenantId)->where('almacen_id', $this->almacen)], 'cantidad')
            ->when($esVendedor, fn ($query) => $query->whereHas('existencias', fn ($existencias) => $existencias
                ->where('tenant_id', $tenantId)->where('almacen_id', $this->almacen)->where('cantidad', '>', 0)))
            ->when(filled($this->busquedaProducto), function ($query): void {
                $termino = trim($this->busquedaProducto);
                $query->where(function ($builder) use ($termino): void {
                    $builder->where('nombre', 'ilike', "%{$termino}%")
                        ->orWhere('sku', 'ilike', "%{$termino}%");
                });
            })
            ->orderBy('nombre')->limit(60)->get(['id', 'sku', 'nombre', 'unidad_medida', 'precio_venta', 'requiere_serie']);
        if ($productosEnCarrito !== []) {
            $productos = $productos->merge(Producto::query()->where('tenant_id', $tenantId)->where('activo', true)
                ->whereIn('id', $productosEnCarrito)
                ->withSum(['existencias as stock_disponible' => fn ($query) => $query
                    ->where('tenant_id', $tenantId)->where('almacen_id', $this->almacen)], 'cantidad')
                ->get(['id', 'sku', 'nombre', 'unidad_medida', 'precio_venta', 'requiere_serie']))
                ->unique('id')->values();
        }
        $unidadesPorProducto = $productosEnCarrito === []
            ? collect()
            : UnidadInventario::query()->where('tenant_id', $tenantId)->whereIn('producto_id', $productosEnCarrito)
                ->where('estado', 'disponible')
                ->whereHas('ubicacion', fn ($query) => $query->where('almacen_id', $this->almacen))
                ->orderBy('numero_serie')->get(['id', 'producto_id', 'numero_serie', 'codigo_activo'])
                ->groupBy('producto_id');

        return view('livewire.ventas.index', compact('ventas', 'productos', 'unidadesPorProducto'))->layout('layouts.app');
    }
}
