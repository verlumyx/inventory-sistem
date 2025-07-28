import { Head, Link, router, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, Edit, QrCode, Calendar, Clock, Package, Warehouse, Barcode, CheckCircle } from 'lucide-react';
import { Separator } from '@/components/ui/separator';
import React from 'react';

interface Item {
    id: number;
    code: string;
    name: string;
    qr_code?: string;
    description?: string;
    price?: number;
    unit?: string;
    status: boolean;
    status_text: string;
    display_name: string;
    short_description: string;
    is_active: boolean;
    is_inactive: boolean;
    created_at: string;
    updated_at: string;
}

interface Warehouse {
    id: number;
    code: string;
    name: string;
    display_name: string;
    quantity_available: number;
}

interface Props {
    item: Item;
    metadata?: {
        created_ago: string;
        updated_ago: string;
        is_recently_created: boolean;
        is_recently_updated: boolean;
    };
    warehouses: Warehouse[];
    totalStock: number;
}

export default function Show({ item, metadata, warehouses, totalStock }: Props) {
    const { flash, errors: pageErrors } = usePage().props as any;

    const breadcrumbs = [
        { title: 'Panel de Control', href: '/dashboard' },
        { title: 'Items', href: '/items' },
        { title: item.name, href: `/items/${item.id}`},
    ];

    return (
        <AuthenticatedLayout breadcrumbs={breadcrumbs}>
            <Head title={item.name} />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <div>
                            <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {item.name}
                            </h1>
                            <div className="flex items-center space-x-2 mt-1">
                                <span className="text-sm font-mono text-gray-600 dark:text-gray-400">
                                    {item.code}
                                </span>
                                <Badge variant={item.status ? 'default' : 'secondary'}>
                                    {item.status_text}
                                </Badge>
                            </div>
                        </div>
                    </div>
                    <div className="flex space-x-2">
                        <Link href="/items">
                            <Button variant="outline">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Volver
                            </Button>
                        </Link>
                        <Link href={`/items/${item.id}/edit`}>
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
                    <div className="lg:col-span-2 space-y-6">
                        {/* Basic Information */}
                        <Card className="shadow-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Package className="h-5 w-5" />
                                    Información Básica
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">Nombre</label>
                                        <p className="text-gray-900 font-medium">{item.name}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">Código</label>
                                        <p className="text-gray-900 font-mono">{item.code}</p>
                                    </div>
                                    {item.price && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-500">Precio</label>
                                            <p className="text-gray-900 font-medium">
                                                ${item.price.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                            </p>
                                        </div>
                                    )}
                                    {item.unit && (
                                        <div>
                                            <label className="text-sm font-medium text-gray-500">Unidad</label>
                                            <p className="text-gray-900">{item.unit}</p>
                                        </div>
                                    )}
                                </div>

                                {item.qr_code && (
                                    <div>
                                        <label className="text-sm font-medium text-gray-500 flex items-center gap-2">
                                            <Barcode className="h-4 w-4" />
                                            Código de barra
                                        </label>
                                        <p className="text-gray-900 font-mono">{item.qr_code}</p>
                                    </div>
                                )}

                                <div>
                                    <label className="text-sm font-medium text-gray-500">Estado</label>
                                    <div className="mt-1">
                                        <Badge variant={item.status ? 'default' : 'secondary'}>
                                            {item.status_text}
                                        </Badge>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Description */}
                        {item.description && (
                            <Card className="shadow-sm">
                                <CardHeader>
                                    <CardTitle>Descripción</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-gray-700 whitespace-pre-wrap">{item.description}</p>
                                </CardContent>
                            </Card>
                        )}

                        {/* Warehouse Availability */}
                        <Card className="shadow-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Warehouse className="h-5 w-5" />
                                    Disponibilidad en Almacenes
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                {warehouses.length > 0 ? (
                                    <>
                                        <div className="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                            <div className="flex items-center justify-between">
                                                <span className="text-sm font-medium text-blue-800">Stock Total:</span>
                                                <span className="text-lg font-bold text-blue-900">
                                                    {totalStock.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} {item.unit || 'unidades'}
                                                </span>
                                            </div>
                                        </div>

                                        <div className="rounded-md border overflow-hidden">
                                            <Table>
                                                <TableHeader>
                                                    <TableRow>
                                                        <TableHead className="py-3">Código</TableHead>
                                                        <TableHead className="py-3">Almacén</TableHead>
                                                        <TableHead className="py-3 text-right">Cantidad Disponible</TableHead>
                                                    </TableRow>
                                                </TableHeader>
                                                <TableBody>
                                                    {warehouses.map((warehouse) => (
                                                        <TableRow key={warehouse.id}>
                                                            <TableCell className="font-mono text-sm">
                                                                {warehouse.code}
                                                            </TableCell>
                                                            <TableCell>
                                                                {warehouse.name}
                                                            </TableCell>
                                                            <TableCell className="text-right font-medium">
                                                                <span className="text-green-700">
                                                                    {warehouse.quantity_available.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                                </span>
                                                                <span className="text-gray-500 ml-1 text-sm">
                                                                    {item.unit || 'unidades'}
                                                                </span>
                                                            </TableCell>
                                                        </TableRow>
                                                    ))}
                                                </TableBody>
                                            </Table>
                                        </div>
                                    </>
                                ) : (
                                    <div className="text-center py-8">
                                        <Package className="h-12 w-12 text-gray-400 mx-auto mb-3" />
                                        <p className="text-gray-500 text-sm">
                                            Este artículo no tiene stock disponible en ningún almacén
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        {/* Quick Actions */}
                        <Card className="shadow-sm">
                            <CardHeader>
                                <CardTitle>Acciones Rápidas</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <Link href={`/items/${item.id}/edit`} className="block">
                                    <Button variant="outline" className="w-full justify-start">
                                        <Edit className="h-4 w-4 mr-2" />
                                        Editar artículo
                                    </Button>
                                </Link>

                                <Separator />

                                <Link href="/items" className="block">
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
                                <CardTitle className="flex items-center gap-2">
                                    <Clock className="h-5 w-5" />
                                    Información de Estado
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <label className="text-sm font-medium text-gray-500 flex items-center gap-2">
                                        <Calendar className="h-4 w-4" />
                                        Fecha de Creación
                                    </label>
                                    <p className="text-gray-900">
                                        {new Date(item.created_at).toLocaleDateString('es-ES', {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        })}
                                    </p>
                                    {metadata?.created_ago && (
                                        <p className="text-sm text-gray-500">{metadata.created_ago}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="text-sm font-medium text-gray-500">Última Actualización</label>
                                    <p className="text-gray-900">
                                        {new Date(item.updated_at).toLocaleDateString('es-ES', {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        })}
                                    </p>
                                    {metadata?.updated_ago && (
                                        <p className="text-sm text-gray-500">{metadata.updated_ago}</p>
                                    )}
                                </div>

                                {metadata?.is_recently_created && (
                                    <div className="bg-green-50 border border-green-200 rounded-md p-3">
                                        <p className="text-sm text-green-800">
                                            ✨ Item creado recientemente
                                        </p>
                                    </div>
                                )}

                                {metadata?.is_recently_updated && (
                                    <div className="bg-blue-50 border border-blue-200 rounded-md p-3">
                                        <p className="text-sm text-blue-800">
                                            🔄 Item actualizado recientemente
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
