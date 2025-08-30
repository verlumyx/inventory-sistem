import React from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, CheckCircle, Clock, DollarSign, Edit, Package, Receipt, Warehouse, Printer, FileText, Eye, ChevronDown } from 'lucide-react';

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
    status: number;
    status_text: string;
    is_pending: boolean;
    is_paid: boolean;
    can_edit: boolean;
    warehouse: {
        id: number;
        code: string;
        name: string;
        display_name: string;
    };
    items: InvoiceItem[];
    total_amount: number;
    formatted_total_amount: string;
    rate: number;
    formatted_rate: string;
    should_show_rate: boolean;
    total_amount_bs: number;
    formatted_total_amount_bs: string;
    items_count: number;
    display_name: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    invoice: Invoice;
}

export default function Show({ invoice }: Props) {
    const { flash, errors: pageErrors } = usePage().props as any;
    const [isPrinting, setIsPrinting] = React.useState(false);
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const handleMarkAsPaid = () => {
        router.patch(
            `/invoices/${invoice.id}/mark-as-paid`,
            {},
            {
                onSuccess: () => {
                    // El redirect se maneja en el controlador
                },
            },
        );
    };

    const handleMarkAsPending = () => {
        router.patch(
            `/invoices/${invoice.id}/mark-as-pending`,
            {},
            {
                onSuccess: () => {
                    // El redirect se maneja en el controlador
                },
            },
        );
    };

    const handlePrint = () => {
        if (isPrinting) return;

        setIsPrinting(true);

        router.post(
            `/invoices/${invoice.id}/print`,
            {},
            {
                onSuccess: (page) => {
                    // La respuesta exitosa se maneja automáticamente
                    setIsPrinting(false);
                },
                onError: (errors) => {
                    setIsPrinting(false);
                    // Los errores se muestran automáticamente en flash messages
                },
                onFinish: () => {
                    setIsPrinting(false);
                },
            },
        );
    };

    const breadcrumbs = [
        { title: 'Panel de Control', href: '/dashboard' },
        { title: 'Facturas', href: '/invoices' },
        { title: invoice.code, href: `/invoices/${invoice.id}` },
    ];

    return (
        <AuthenticatedLayout breadcrumbs={breadcrumbs}>
            <Head title={`Factura ${invoice.code}`} />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <div>
                            <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">{invoice.code}</h1>
                            <div className="mt-1 flex items-center space-x-2">
                                <span className="font-mono text-sm text-gray-600 dark:text-gray-400">{invoice.warehouse.display_name}</span>
                                <Badge
                                    variant={invoice.is_paid ? 'default' : 'secondary'}
                                    className={invoice.is_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}
                                >
                                    {invoice.is_paid ? (
                                        <>
                                            <CheckCircle className="mr-1 h-3 w-3" />
                                            {invoice.status_text}
                                        </>
                                    ) : (
                                        <>
                                            <Clock className="mr-1 h-3 w-3" />
                                            {invoice.status_text}
                                        </>
                                    )}
                                </Badge>
                            </div>
                        </div>
                    </div>
                    <div className="flex space-x-2">
                        <Link href="/invoices">
                            <Button variant="outline">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Volver
                            </Button>
                        </Link>

                        {/* Botones de status */}
                        {invoice.is_pending && (
                            <Button onClick={handleMarkAsPaid} className="bg-green-600 hover:bg-green-700">
                                <DollarSign className="mr-2 h-4 w-4" />
                                Marcar como Pagada
                            </Button>
                        )}

                        {invoice.is_paid && (
                            <>
                                <Button onClick={handleMarkAsPending} variant="outline">
                                    <Clock className="mr-2 h-4 w-4" />
                                    Marcar como Por Pagar
                                </Button>

                                {/* Botón de impresión solo para facturas pagadas */}
                                <Button
                                    onClick={handlePrint}
                                    disabled={isPrinting}
                                    className="bg-blue-600 hover:bg-blue-700"
                                >
                                    <Printer className="mr-2 h-4 w-4" />
                                    {isPrinting ? 'Imprimiendo...' : 'Imprimir'}
                                </Button>

                                {/* Menú desplegable PDF térmico 58mm */}
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button
                                            className="bg-orange-600 hover:bg-orange-700 font-bold"
                                            variant="outline"
                                        >
                                            <FileText className="mr-2 h-4 w-4" />
                                            🎫 PDF 58mm
                                            <ChevronDown className="ml-2 h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            onClick={() => window.open(route('invoices.pdf.thermal.preview', invoice.id), '_blank')}
                                        >
                                            <Eye className="mr-2 h-4 w-4" />
                                            Vista Previa
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            onClick={() => window.open(route('invoices.pdf.thermal.print', invoice.id), '_blank')}
                                        >
                                            <Printer className="mr-2 h-4 w-4" />
                                            Imprimir Directo
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </>
                        )}

                        {/* Botón de editar solo si puede editarse */}
                        {invoice.can_edit && (
                            <Link href={`/invoices/${invoice.id}/edit`}>
                                <Button>
                                    <Edit className="mr-2 h-4 w-4" />
                                    Editar
                                </Button>
                            </Link>
                        )}
                    </div>
                </div>

                {/* Flash Messages */}
                {flash?.success && (
                    <div className="flex items-center gap-2 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-green-700">
                        <CheckCircle className="h-4 w-4" />
                        {flash.success}
                    </div>
                )}

                {flash?.error && (
                    <div className="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                        <div className="flex items-start">
                            <div className="flex-shrink-0">
                                <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fillRule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <h3 className="text-sm font-medium text-red-800">Error</h3>
                                <div className="mt-2 text-sm text-red-700">
                                    <pre className="whitespace-pre-wrap font-sans">{flash.error}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {pageErrors?.error && (
                    <div className="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                        <div className="flex items-start">
                            <div className="flex-shrink-0">
                                <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fillRule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <h3 className="text-sm font-medium text-red-800">Error</h3>
                                <div className="mt-2 text-sm text-red-700">
                                    <pre className="whitespace-pre-wrap font-sans">{pageErrors.error}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Información General */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
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
                                <p className="font-mono text-lg">{invoice.code}</p>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-gray-500">Total de Items</label>
                                <p className="text-lg">
                                    <Badge variant="outline" className="text-base">
                                        {invoice.items_count} items
                                    </Badge>
                                </p>
                            </div>
                            {invoice.should_show_rate && (
                                <div>
                                    <label className="text-sm font-medium text-gray-500">Tasa de Cambio</label>
                                    <p className="text-lg font-semibold text-blue-600">{invoice.formatted_rate}</p>
                                </div>
                            )}
                            <div>
                                <label className="text-sm font-medium text-gray-500">Monto Total</label>
                                <p className="text-2xl font-bold text-green-600">{invoice.formatted_total_amount}</p>
                                {invoice.should_show_rate && (
                                    <p className="mt-1 text-lg font-semibold text-blue-600">Total Bs: {invoice.formatted_total_amount_bs}</p>
                                )}
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
                                <p className="font-mono text-lg">{invoice.warehouse.code}</p>
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
                        <CardDescription>Detalle de todos los items incluidos en esta factura</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {invoice.items.length === 0 ? (
                            <div className="py-8 text-center">
                                <Package className="mx-auto mb-4 h-12 w-12 text-gray-400" />
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
                                                        <div className="text-sm text-gray-500">{invoiceItem.item.unit}</div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <code className="rounded bg-gray-100 px-2 py-1 text-sm">{invoiceItem.item.code}</code>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <span className="font-medium">{invoiceItem.formatted_amount}</span>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <span className="font-medium">
                                                        {invoice.should_show_rate
                                                            ? `Bs ${(invoiceItem.price * invoice.rate).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                                                            : invoiceItem.formatted_price
                                                        }
                                                    </span>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <span className="font-bold text-green-600">
                                                        {invoice.should_show_rate
                                                            ? `Bs ${(invoiceItem.subtotal * invoice.rate).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                                                            : invoiceItem.formatted_subtotal
                                                        }
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
                                                <span className="text-xl font-bold text-green-600">{invoice.formatted_total_amount}</span>
                                            </TableCell>
                                        </TableRow>
                                        {/* Fila de Total en Bolívares si la tasa es diferente de 1 */}
                                        {invoice.should_show_rate && (
                                            <TableRow className="bg-blue-50">
                                                <TableCell colSpan={4} className="text-right font-bold text-blue-800">
                                                    TOTAL Bs:
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <span className="text-xl font-bold text-blue-600">{invoice.formatted_total_amount_bs}</span>
                                                </TableCell>
                                            </TableRow>
                                        )}
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
