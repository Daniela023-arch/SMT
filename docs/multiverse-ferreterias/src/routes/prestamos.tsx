import { createFileRoute, Navigate } from "@tanstack/react-router";
export const Route=createFileRoute("/prestamos")({component:()=> <Navigate to="/login" />});
