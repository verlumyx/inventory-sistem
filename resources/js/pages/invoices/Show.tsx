import React from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, Edit, Receipt, Package, Warehouse } from 'lucide-react';

interface InvoiceItem {
    id: number;
    item_id: number;
    amount: number;
    price: number;
    subtotal: number;
    formatted_amount: string;
    formatted_price: string;
    formatted_subtotal: string;
    item: {
        id: number;
        code: string;
        name: string;
        unit: string;
        display_name: string;
    };
}

interface Invoice {
    id: number;
    code: string;
    warehouse_id: number;
    warehouse: {
        id: number;
        code: string;
        name: string;
        display_name: string;
    };
    items: InvoiceItem[];
    total_amount: number;
    formatted_total_amount: string;
    items_count: number;
    display_name: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    invoice: Invoice;
}

export default function Show({ invoice }: Props) {
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    };

    return (
        <AuthenticatedLayout>
            <Head title={`Factura ${invoice.code}`} />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">{invoice.code}</h1>
                            <p className="text-muted-foreground">
                                {invoice.warehouse.display_name}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <div className="flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => router.visit(route('invoices.index'))}
                            >
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Volver
                            </Button>
                            <Button
                                variant="outline"
                                onClick={() => router.visit(route('invoices.edit', invoice.id))}
                            >
                                <Edit className="mr-2 h-4 w-4" />
                                Editar
                            </Button>
                        </div>
                    </div>
                </div>

                {/* Información General */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <Card>
                                    <CardHeader>
                                        <CardTitle className="flex items-center gap-2">
                                            <Receipt className="h-5 w-5" />
                                            Información de la Factura
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <div>
                                            <label className="text-sm font-medium text-gray-500">Código</label>
                                            <p className="text-lg font-mono">{invoice.code}</p>
                                        </div>
                                        <div>
                                            <label className="text-sm font-medium text-gray-500">Total de Items</label>
                                            <p className="text-lg">
                                                <Badge variant="outline" className="text-base">
                                                    {invoice.items_count} items
                                                </Badge>
                                            </p>
                                        </div>
                                        <div>
                                            <label className="text-sm font-medium text-gray-500">Monto Total</label>
                                            <p className="text-2xl font-bold text-green-600">
                                                {invoice.formatted_total_amount}
                                            </p>
                                        </div>
                                    </CardContent>
                                </Card>

                                <Card>
                                    <CardHeader>
                                        <CardTitle className="flex items-center gap-2">
                                            <Warehouse className="h-5 w-5" />
                                            Almacén
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <div>
                                            <label className="text-sm font-medium text-gray-500">Código</label>
                                            <p className="text-lg font-mono">{invoice.warehouse.code}</p>
                                        </div>
                                        <div>
                                            <label className="text-sm font-medium text-gray-500">Nombre</label>
                                            <p className="text-lg">{invoice.warehouse.name}</p>
                                        </div>
                                        <div>
                                            <label className="text-sm font-medium text-gray-500">Fechas</label>
                                            <div className="space-y-1">
                                                <p className="text-sm">
                                                    <span className="font-medium">Creada:</span> {formatDate(invoice.created_at)}
                                                </p>
                                                <p className="text-sm">
                                                    <span className="font-medium">Actualizada:</span> {formatDate(invoice.updated_at)}
                                                </p>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>

                            {/* Items de la Factura */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Package className="h-5 w-5" />
                                        Items de la Factura
                                    </CardTitle>
                                    <CardDescription>
                                        Detalle de todos los items incluidos en esta factura
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    {invoice.items.length === 0 ? (
                                        <div className="text-center py-8">
                                            <Package className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                                            <p className="text-gray-500">No hay items en esta factura</p>
                                        </div>
                                    ) : (
                                        <div className="rounded-md border">
                                            <Table>
                                                <TableHeader>
                                                    <TableRow>
                                                        <TableHead>Item</TableHead>
                                                        <TableHead>Código</TableHead>
                                                        <TableHead className="text-right">Cantidad</TableHead>
                                                        <TableHead className="text-right">Precio Unit.</TableHead>
                                                        <TableHead className="text-right">Subtotal</TableHead>
                                                    </TableRow>
                                                </TableHeader>
                                                <TableBody>
                                                    {invoice.items.map((invoiceItem) => (
                                                        <TableRow key={invoiceItem.id}>
                                                            <TableCell>
                                                                <div>
                                                                    <div className="font-medium">{invoiceItem.item.name}</div>
                                                                    <div className="text-sm text-gray-500">
                                                                        {invoiceItem.item.unit}
                                                                    </div>
                                                                </div>
                                                            </TableCell>
                                                            <TableCell>
                                                                <code className="text-sm bg-gray-100 px-2 py-1 rounded">
                                                                    {invoiceItem.item.code}
                                                                </code>
                                                            </TableCell>
                                                            <TableCell className="text-right">
                                                                <span className="font-medium">
                                                                    {invoiceItem.formatted_amount}
                                                                </span>
                                                            </TableCell>
                                                            <TableCell className="text-right">
                                                                <span className="font-medium">
                                                                    {invoiceItem.formatted_price}
                                                                </span>
                                                            </TableCell>
                                                            <TableCell className="text-right">
                                                                <span className="font-bold text-green-600">
                                                                    {invoiceItem.formatted_subtotal}
                                                                </span>
                                                            </TableCell>
                                                        </TableRow>
                                                    ))}
                                                    {/* Fila de Total */}
                                                    <TableRow className="border-t-2 bg-gray-50">
                                                        <TableCell colSpan={4} className="text-right font-bold">
                                                            TOTAL:
                                                        </TableCell>
                                                        <TableCell className="text-right">
                                                            <span className="text-xl font-bold text-green-600">
                                                                {invoice.formatted_total_amount}
                                                            </span>
                                                        </TableCell>
                                                    </TableRow>
                                                </TableBody>
                                            </Table>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
            </div>
        </AuthenticatedLayout>
    );
}
