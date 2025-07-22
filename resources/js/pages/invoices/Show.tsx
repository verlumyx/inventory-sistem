import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
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

    const breadcrumbs = [
        { title: 'Panel de Control', href: '/dashboard' },
        { title: 'Facturas', href: '/invoices' },
        { title: invoice.code, href: `/invoices/${invoice.id}` },
    ];

    return (
        <AuthenticatedLayout breadcrumbs={breadcrumbs}>
            <Head title={`Factura ${invoice.code}`} />

            <div className="p-6 space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <div>
                            <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {invoice.code}
                            </h1>
                            <div className="flex items-center space-x-2 mt-1">
                                <span className="text-sm font-mono text-gray-600 dark:text-gray-400">
                                    {invoice.warehouse.display_name}
                                </span>
                                {/*<Badge variant={warehouse.status ? 'default' : 'secondary'}>*/}
                                {/*    {warehouse.status_text}*/}
                                {/*</Badge>*/}
                            </div>
                        </div>
                    </div>
                    <div className="flex space-x-2">
                        <Link href="/invoices">
                            <Button variant="outline">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Volver
                            </Button>
                        </Link>
                        <Link href={`/invoices/${invoice.id}/edit`}>
                            <Button>
                                <Edit className="h-4 w-4 mr-2" />
                                Editar
                            </Button>
                        </Link>
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
