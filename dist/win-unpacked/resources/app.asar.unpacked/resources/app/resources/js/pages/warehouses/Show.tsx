import { Head, Link, router, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Edit, Calendar, Clock, Hash, FileText, ToggleLeft, CheckCircle } from 'lucide-react';
import { BreadcrumbItem } from '@/types';
import React from 'react';

interface Warehouse {
    id: number;
    code: string;
    name: string;
    description: string | null;
    status: boolean;
    status_text: string;
    default: boolean;
    default_text: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    warehouse: Warehouse;
}

export default function Show({ warehouse }: Props) {
    const { flash, errors: pageErrors } = usePage().props as any;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Panel de Control', href: '/dashboard' },
        { title: 'Almacenes', href: '/warehouses' },
        { title: warehouse.code, href: `/warehouses/${warehouse.id}` },
    ];

    return (
        <AuthenticatedLayout breadcrumbs={breadcrumbs}>
            <Head title={warehouse.name} />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <div>
                            <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {warehouse.name}
                            </h1>
                            <div className="flex items-center space-x-2 mt-1">
                                <span className="text-sm font-mono text-gray-600 dark:text-gray-400">
                                    {warehouse.code}
                                </span>
                                <Badge variant={warehouse.status ? 'default' : 'secondary'}>
                                    {warehouse.status_text}
                                </Badge>
                            </div>
                        </div>
                    </div>
                    <div className="flex space-x-2">
                        <Link href="/warehouses">
                            <Button variant="outline">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Volver
                            </Button>
                        </Link>
                        <Link href={`/warehouses/${warehouse.id}/edit`}>
                            <Button>
                                <Edit className="h-4 w-4 mr-2" />
                                Editar
                            </Button>
                        </Link>
                    </div>
                </div>
                {flash?.success && (
                    <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md flex items-center gap-2">
                        <CheckCircle className="h-4 w-4" />
                        {flash.success}
                    </div>
                )}

                {flash?.error && (
                    <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                        {flash.error}
                    </div>
                )}

                {pageErrors?.error && (
                    <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                        {pageErrors.error}
                    </div>
                )}

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Information */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Basic Information */}
                        <Card className="shadow-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <FileText className="h-5 w-5 mr-2" />
                                    Información Básica
                                </CardTitle>
                                <CardDescription>
                                    Detalles principales del almacén
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <Label className="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Código
                                        </Label>
                                        <div className="flex items-center mt-1">
                                            <Hash className="h-4 w-4 mr-2 text-gray-400" />
                                            <span className="font-mono text-sm">
                                                {warehouse.code}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <Label className="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Estado
                                        </Label>
                                        <div className="flex items-center mt-1">
                                            <ToggleLeft className="h-4 w-4 mr-2 text-gray-400" />
                                            <Badge variant={warehouse.status ? 'default' : 'secondary'}>
                                                {warehouse.status_text}
                                            </Badge>
                                        </div>
                                    </div>

                                    <div>
                                        <Label className="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Por Defecto
                                        </Label>
                                        <div className="flex items-center mt-1">
                                            <ToggleLeft className="h-4 w-4 mr-2 text-gray-400" />
                                            <Badge
                                                variant={warehouse.default ? 'default' : 'outline'}
                                                className={warehouse.default ? 'bg-green-100 text-green-800 border-green-200' : ''}
                                            >
                                                {warehouse.default_text}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <Label className="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        Nombre
                                    </Label>
                                    <p className="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        {warehouse.name}
                                    </p>
                                </div>

                                {warehouse.description && (
                                    <div>
                                        <Label className="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Descripción
                                        </Label>
                                        <p className="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">
                                            {warehouse.description}
                                        </p>
                                    </div>
                                )}

                                {!warehouse.description && (
                                    <div>
                                        <Label className="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Descripción
                                        </Label>
                                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400 italic">
                                            Sin descripción
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Additional Information */}
                        <Card className="shadow-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <Clock className="h-5 w-5 mr-2" />
                                    Información del Sistema
                                </CardTitle>
                                <CardDescription>
                                    Fechas y metadatos del almacén
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <Label className="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Fecha de Creación
                                        </Label>
                                        <div className="flex items-center mt-1">
                                            <Calendar className="h-4 w-4 mr-2 text-gray-400" />
                                            <span className="text-sm">
                                                {new Date(warehouse.created_at).toLocaleString('es-ES', {
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric',
                                                    hour: '2-digit',
                                                    minute: '2-digit'
                                                })}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <Label className="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Última Actualización
                                        </Label>
                                        <div className="flex items-center mt-1">
                                            <Clock className="h-4 w-4 mr-2 text-gray-400" />
                                            <span className="text-sm">
                                                {new Date(warehouse.updated_at).toLocaleString('es-ES', {
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric',
                                                    hour: '2-digit',
                                                    minute: '2-digit'
                                                })}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Quick Actions */}
                        <Card className="shadow-sm">
                            <CardHeader>
                                <CardTitle className="text-lg">Acciones Rápidas</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <Link href={`/warehouses/${warehouse.id}/edit`} className="block">
                                    <Button variant="outline" className="w-full justify-start">
                                        <Edit className="h-4 w-4 mr-2" />
                                        Editar Almacén
                                    </Button>
                                </Link>
                                
                                <Separator />
                                
                                <Link href="/warehouses" className="block">
                                    <Button variant="ghost" className="w-full justify-start">
                                        <ArrowLeft className="h-4 w-4 mr-2" />
                                        Volver a la Lista
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>

                        {/* Status Information */}
                        <Card className="shadow-sm">
                            <CardHeader>
                                <CardTitle className="text-lg">Estado del Almacén</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="flex items-center justify-center p-4">
                                    <div className="text-center">
                                        <div className={`inline-flex items-center justify-center w-16 h-16 rounded-full mb-3 ${
                                            warehouse.status 
                                                ? 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400' 
                                                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
                                        }`}>
                                            <ToggleLeft className="h-8 w-8" />
                                        </div>
                                        <p className="text-sm font-medium">
                                            {warehouse.status_text}
                                        </p>
                                        <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {warehouse.status 
                                                ? 'El almacén está disponible para operaciones'
                                                : 'El almacén no está disponible para operaciones'
                                            }
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

// Helper component for labels
function Label({ children, className = '' }: { children: React.ReactNode; className?: string }) {
    return (
        <label className={`block text-sm font-medium ${className}`}>
            {children}
        </label>
    );
}
