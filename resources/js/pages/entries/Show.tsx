import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Edit, Package, Calendar, User, MapPin, Hash, CheckCircle } from 'lucide-react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';

interface Item {
    id: number;
    code: string;
    name: string;
    display_name: string;
    price?: number;
    unit?: string;
}

interface Warehouse {
    id: number;
    code: string;
    name: string;
    display_name: string;
}

interface EntryItem {
    id: number;
    item_id: number;
    warehouse_id: number;
    amount: number;
    formatted_amount: string;
    display_info: string;
    item: Item;
    warehouse: Warehouse;
}

interface Entry {
    id: number;
    code: string;
    name: string;
    description?: string;
    status: number; // 0 = Por recibir, 1 = Recibido
    status_text: string;
    display_name: string;
    short_description: string;
    is_pending: boolean; // status === 0
    is_received: boolean; // status === 1
    created_at: string;
    updated_at: string;
}

interface Metadata {
    created_ago: string;
    updated_ago: string;
    is_recently_created: boolean;
    is_recently_updated: boolean;
    total_items: number;
    total_amount: number;
}

interface Props {
    entry: Entry;
    items: EntryItem[];
    metadata: Metadata;
}

export default function Show({ entry, items, metadata }: Props) {
    const [showConfirmDialog, setShowConfirmDialog] = useState(false);

    const handleReceiveEntry = () => {
        router.patch(route('entries.receive', entry.id), {}, {
            onSuccess: () => {
                // El redirect se maneja en el controlador
            },
        });
        setShowConfirmDialog(false);
    };
    return (
        <AuthenticatedLayout>
            <Head title={`Entrada - ${entry.code}`} />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">{entry.code}</h1>
                            <p className="text-muted-foreground">
                                {entry.short_description}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Badge
                            variant={entry.status === 1 ? "default" : "secondary"}
                            className={entry.status === 1 ? "bg-green-100 text-green-800" : "bg-yellow-100 text-yellow-800"}
                        >
                            {entry.status === 1 ? "Recibido" : "Por recibir"}
                        </Badge>
                        <div className="flex gap-2">
                            <Link href={route('entries.index')}>
                                <Button variant="outline" size="sm">
                                    <ArrowLeft className="mr-2 h-4 w-4" />
                                    Volver
                                </Button>
                            </Link>
                            {entry.status === 0 && (
                                <Link href={route('entries.edit', entry.id)}>
                                    <Button variant="outline">
                                        <Edit className="mr-2 h-4 w-4" />
                                        Editar
                                    </Button>
                                </Link>
                            )}
                            {entry.status === 0 && (
                                <AlertDialog open={showConfirmDialog} onOpenChange={setShowConfirmDialog}>
                                    <AlertDialogTrigger asChild>
                                        <Button className="bg-green-600 hover:bg-green-700">
                                            <CheckCircle className="mr-2 h-4 w-4" />
                                            Recibir
                                        </Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Confirmar Recepción</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                ¿Está seguro de que desea marcar la entrada <strong>{entry.code}</strong> como recibida?
                                                <br />
                                                Esta acción no se puede deshacer.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>Cancelar</AlertDialogCancel>
                                            <AlertDialogAction
                                                onClick={handleReceiveEntry}
                                                className="bg-green-600 hover:bg-green-700"
                                            >
                                                Sí, marcar como recibida
                                            </AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            )}
                        </div>
                    </div>
                </div>

                {/* Información General */}
                <div className="grid gap-6 md:grid-cols-2">
                    {/* Detalles de la Entrada */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Package className="h-5 w-5" />
                                Información de la Entrada
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 gap-4">
                                <div>
                                    <label className="text-sm font-medium text-gray-500">Nombre</label>
                                    <p className="text-gray-900 font-medium">{entry.name}</p>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-gray-500">Código</label>
                                    <p className="text-gray-900 font-mono">{entry.code}</p>
                                </div>
                                {entry.description && (
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">Descripción</label>
                                        <p className="text-gray-900">{entry.description}</p>
                                    </div>
                                )}
                                <div>
                                    <label className="text-sm font-medium text-gray-500">Estado</label>
                                    <div className="mt-1">
                                        <Badge 
                                            variant={entry.status ? "default" : "secondary"}
                                            className={entry.status ? "bg-green-100 text-green-800" : ""}
                                        >
                                            {entry.status_text}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Estadísticas */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Hash className="h-5 w-5" />
                                Estadísticas
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div className="text-center p-4 bg-blue-50 rounded-lg">
                                    <div className="text-2xl font-bold text-blue-600">{metadata.total_items}</div>
                                    <div className="text-sm text-blue-600">Items</div>
                                </div>
                                <div className="text-center p-4 bg-green-50 rounded-lg">
                                    <div className="text-2xl font-bold text-green-600">
                                        {metadata.total_amount.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                    </div>
                                    <div className="text-sm text-green-600">Cantidad Total</div>
                                </div>
                            </div>
                            
                            <div className="space-y-2 pt-4 border-t">
                                <div className="flex items-center gap-2 text-sm">
                                    <Calendar className="h-4 w-4 text-gray-500" />
                                    <span className="text-gray-500">Creado:</span>
                                    <span className="font-medium">{metadata.created_ago}</span>
                                    {metadata.is_recently_created && (
                                        <Badge variant="outline" className="text-xs">Reciente</Badge>
                                    )}
                                </div>
                                <div className="flex items-center gap-2 text-sm">
                                    <Calendar className="h-4 w-4 text-gray-500" />
                                    <span className="text-gray-500">Actualizado:</span>
                                    <span className="font-medium">{metadata.updated_ago}</span>
                                    {metadata.is_recently_updated && (
                                        <Badge variant="outline" className="text-xs">Reciente</Badge>
                                    )}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Items de la Entrada */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Package className="h-5 w-5" />
                            Items de la Entrada
                        </CardTitle>
                        <CardDescription>
                            {metadata.total_items > 0 
                                ? `${metadata.total_items} item${metadata.total_items !== 1 ? 's' : ''} en esta entrada`
                                : 'No hay items en esta entrada'
                            }
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {items.length > 0 ? (
                            <div className="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead className="py-4">Item</TableHead>
                                            <TableHead className="py-4">Código</TableHead>
                                            <TableHead className="py-4">Almacén</TableHead>
                                            <TableHead className="py-4">Cantidad</TableHead>
                                            <TableHead className="py-4">Precio Unit.</TableHead>
                                            <TableHead className="py-4">Total</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {items.map((entryItem) => (
                                            <TableRow key={entryItem.id}>
                                                <TableCell className="py-4">
                                                    <div>
                                                        <div className="font-medium">{entryItem.item.name}</div>
                                                        <div className="text-sm text-gray-500">{entryItem.item.display_name}</div>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="font-mono py-4">
                                                    {entryItem.item.code}
                                                </TableCell>
                                                <TableCell className="py-4">
                                                    <div className="flex items-center gap-2">
                                                        <MapPin className="h-4 w-4 text-gray-500" />
                                                        <div>
                                                            <div className="font-medium">{entryItem.warehouse.name}</div>
                                                            <div className="text-sm text-gray-500">{entryItem.warehouse.code}</div>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="py-4">
                                                    <div className="font-medium">
                                                        {entryItem.amount.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                    </div>
                                                    {entryItem.item.unit && (
                                                        <div className="text-sm text-gray-500">{entryItem.item.unit}</div>
                                                    )}
                                                </TableCell>
                                                <TableCell className="py-4">
                                                    {entryItem.item.price ? (
                                                        <span className="font-medium">
                                                            ${entryItem.item.price.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                        </span>
                                                    ) : (
                                                        <span className="text-gray-400">-</span>
                                                    )}
                                                </TableCell>
                                                <TableCell className="py-4">
                                                    {entryItem.item.price ? (
                                                        <span className="font-medium text-green-600">
                                                            ${(entryItem.item.price * entryItem.amount).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                        </span>
                                                    ) : (
                                                        <span className="text-gray-400">-</span>
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>

                                {/* Totales */}
                                <div className="border-t bg-gray-50 p-4">
                                    <div className="flex justify-between items-center">
                                        <div className="font-medium">
                                            Total de la Entrada:
                                        </div>
                                        <div className="text-lg font-bold text-green-600">
                                            ${items.reduce((total, item) => {
                                                return total + (item.item.price ? item.item.price * item.amount : 0);
                                            }, 0).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                        </div>
                                    </div>
                                    <div className="flex justify-between items-center text-sm text-gray-600 mt-1">
                                        <div>
                                            {metadata.total_items} item{metadata.total_items !== 1 ? 's' : ''} • {metadata.total_amount.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} unidades totales
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <div className="text-center py-12">
                                <Package className="mx-auto h-12 w-12 text-gray-400" />
                                <h3 className="mt-4 text-lg font-semibold text-gray-900">No hay items</h3>
                                <p className="mt-2 text-gray-500">
                                    Esta entrada no tiene items asociados.
                                </p>
                                <Link href={route('entries.edit', entry.id)} className="mt-4 inline-block">
                                    <Button variant="outline">
                                        <Edit className="mr-2 h-4 w-4" />
                                        Agregar Items
                                    </Button>
                                </Link>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Información Adicional */}
                <Card>
                    <CardHeader>
                        <CardTitle>Información Adicional</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <label className="font-medium text-gray-500">ID de la Entrada</label>
                                <p className="text-gray-900">{entry.id}</p>
                            </div>
                            <div>
                                <label className="font-medium text-gray-500">Fecha de Creación</label>
                                <p className="text-gray-900">{new Date(entry.created_at).toLocaleString('es-ES')}</p>
                            </div>
                            <div>
                                <label className="font-medium text-gray-500">Última Modificación</label>
                                <p className="text-gray-900">{new Date(entry.updated_at).toLocaleString('es-ES')}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
