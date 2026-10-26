import { createFileRoute } from '@tanstack/react-router';
import { Workspace } from '@/components/inventra/Workspace';
export const Route = createFileRoute('/admin')({ component: () => <Workspace role="admin" /> });
