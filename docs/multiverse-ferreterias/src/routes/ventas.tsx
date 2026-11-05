import { createFileRoute } from '@tanstack/react-router';
import { Workspace } from '@/components/multiverse/Workspace';
export const Route = createFileRoute('/ventas')({ component: () => <Workspace role="ventas" /> });
