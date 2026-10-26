import { createFileRoute } from '@tanstack/react-router';
import { Workspace } from '@/components/inventra/Workspace';
export const Route = createFileRoute('/ventas')({ component: () => <Workspace role="ventas" /> });
