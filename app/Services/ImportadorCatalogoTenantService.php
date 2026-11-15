<?php

namespace App\Services;

use App\Models\ImportacionCsv;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ImportadorCatalogoTenantService
{
    /**
     * Importa SKU, nombre, unidad_medida, precio_venta, stock_minimo y descripcion.
     * Las filas con SKU existente actualizan ese producto dentro del mismo tenant.
     */
    public function importar(int $tenantId, User $usuario, UploadedFile $archivo): ImportacionCsv
    {
        if (! in_array(strtolower($archivo->getClientOriginalExtension()), ['csv', 'txt'], true)) {
            throw ValidationException::withMessages(['archivoCsv' => 'El archivo debe estar en formato CSV.']);
        }

        $path = $archivo->store('importaciones-csv/'.$tenantId, 'local');
        $stream = Storage::disk('local')->readStream($path);
        if (! is_resource($stream)) {
            throw new \RuntimeException('No se pudo leer el archivo CSV guardado.');
        }

        $total = 0;
        $exitosas = 0;
        $errores = [];
        $firstLine = fgets($stream);
        $delimiter = is_string($firstLine) && substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($stream);
        $headers = fgetcsv($stream, null, $delimiter, '"', '\\');
        if (! is_array($headers)) {
            fclose($stream);
            throw ValidationException::withMessages(['archivoCsv' => 'El archivo CSV está vacío.']);
        }
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]) ?? $headers[0];
        $headers = array_map(fn ($header) => mb_strtolower(trim((string) $header)), $headers);
        $requeridas = ['sku', 'nombre'];
        if (array_diff($requeridas, $headers) !== []) {
            fclose($stream);
            throw ValidationException::withMessages(['archivoCsv' => 'El CSV debe incluir las columnas sku y nombre.']);
        }

        $linea = 1;
        while (($values = fgetcsv($stream, null, $delimiter, '"', '\\')) !== false) {
            $linea++;
            if ($values === [null] || $values === []) {
                continue;
            }
            $total++;
            if ($total > 5000) {
                $errores[] = ['fila' => $linea, 'mensaje' => 'Se detuvo la importación: el límite es 5,000 filas.'];
                break;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = trim((string) ($values[$index] ?? ''));
            }
            $sku = $row['sku'] ?? '';
            $nombre = $row['nombre'] ?? '';
            $price = $row['precio_venta'] ?? '';
            $minimum = $row['stock_minimo'] ?? '';
            if ($sku === '' || mb_strlen($sku) > 255 || $nombre === '' || mb_strlen($nombre) > 255) {
                $errores[] = ['fila' => $linea, 'mensaje' => 'SKU y nombre son obligatorios y deben tener máximo 255 caracteres.'];
                continue;
            }
            if (($price !== '' && (! is_numeric($price) || (float) $price < 0)) || ($minimum !== '' && (! is_numeric($minimum) || (float) $minimum < 0))) {
                $errores[] = ['fila' => $linea, 'sku' => $sku, 'mensaje' => 'Precio y stock mínimo deben ser números mayores o iguales a cero.'];
                continue;
            }

            try {
                DB::transaction(function () use ($tenantId, $row, $sku, $nombre, $price, $minimum): void {
                    $producto = Producto::query()->withTrashed()->firstOrNew(['tenant_id' => $tenantId, 'sku' => $sku]);
                    $producto->forceFill([
                        'nombre' => $nombre,
                        'unidad_medida' => ($row['unidad_medida'] ?? '') ?: 'pieza',
                        'precio_venta' => $price === '' ? null : $price,
                        'stock_minimo' => $minimum === '' ? 0 : $minimum,
                        'descripcion' => ($row['descripcion'] ?? '') ?: null,
                        'activo' => true,
                    ]);
                    if ($producto->trashed()) {
                        $producto->restore();
                    } else {
                        $producto->save();
                    }
                });
                $exitosas++;
            } catch (Throwable $exception) {
                report($exception);
                $errores[] = ['fila' => $linea, 'sku' => $sku, 'mensaje' => 'No se pudo guardar este producto. Verifica sus datos.'];
            }
        }
        fclose($stream);

        return ImportacionCsv::query()->create([
            'tenant_id' => $tenantId,
            'usuario_id' => $usuario->getKey(),
            'archivo' => $path,
            'estado' => $errores === [] ? 'completada' : ($exitosas > 0 ? 'completada_con_errores' : 'fallida'),
            'filas_totales' => $total,
            'filas_exitosas' => $exitosas,
            'filas_fallidas' => count($errores),
            'errores' => $errores,
        ]);
    }
}
