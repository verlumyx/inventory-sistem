import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Plus, Search, Filter, Receipt, Eye, Edit, MoreHorizontal } from 'lucide-react';

interface Invoice {
    id: number;
    code: string;
    warehouse_id: number;
    warehouse: {
        id: number;
        code: string;
        name: string;
    };
    total_amount: number;
    items_count: number;
    display_name: string;
    created_at: string;
    updated_at: string;
}

interface Warehouse {
    id: number;
    code: string;
    name: string;
    display_name: string;
}

interface Props {
    invoices: {
        data: Invoice[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        search?: string;
        warehouse_id?: number;
    };
    pagination: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
}

export default function Index({ invoices, filters, pagination }: Props) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [showFilters, setShowFilters] = useState(false);
    const [warehouseFilter, setWarehouseFilter] = useState(
        filters.warehouse_id ? filters.warehouse_id.toString() : 'all'
    );

    const handleSearch = (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        
        const params: any = {};
        
        if (searchTerm) params.search = searchTerm;
        if (warehouseFilter !== 'all') params.warehouse_id = parseInt(warehouseFilter);
        
        router.get(route('invoices.index'), params, { 
            preserveState: true,
            replace: true 
        });
    };

    const handleWarehouseChange = (value: string) => {
        setWarehouseFilter(value);
        
        const params: any = {};
        if (searchTerm) params.search = searchTerm;
        if (value !== 'all') params.warehouse_id = parseInt(value);
        
        router.get(route('invoices.index'), params, { 
            preserveState: true,
            replace: true 
        });
    };

    const clearFilters = () => {
        setSearchTerm('');
        setWarehouseFilter('all');
        router.get(route('invoices.index'), {}, { 
            preserveState: true,
            replace: true 
        });
    };

    const hasFilters = !!(filters.search || filters.warehouse_id);

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const breadcrumbs = [
        { title: 'Panel de Control', href: '/dashboard' },
        { title: 'Facturas', href: '/invoices' },
    ];

    return (
        <AuthenticatedLayout breadcrumbs={breadcrumbs}>
            <Head title="Facturas" />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Facturas</h1>
                        <p className="text-muted-foreground">
                            Gestiona las facturas de inventario y sus items
                        </p>
                    </div>
                    <Button onClick={() => router.visit(route('invoices.create'))}>
                        <Plus className="mr-2 h-4 w-4" />
                        Nueva Factura
                    </Button>
                </div>
                {/* Estadísticas */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <Card>
                                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                        <CardTitle className="text-sm font-medium">
                                            Total Facturas
                                        </CardTitle>
                                        <Receipt className="h-4 w-4 text-muted-foreground" />
                                    </CardHeader>
                                    <CardContent>
                                        <div className="text-2xl font-bold">{pagination.total}</div>
                                    </CardContent>
                                </Card>
                            </div>

                            {/* Filtros */}
                            <Card className="mb-6">
                                <CardHeader>
                                    <div className="flex justify-between items-center">
                                        <div>
                                            <CardTitle>Buscar Facturas</CardTitle>
                                            <CardDescription>
                                                Encuentra facturas por código o almacén
                                            </CardDescription>
                                        </div>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => setShowFilters(!showFilters)}
                                        >
                                            <Filter className="h-4 w-4 mr-2" />
                                            Mostrar Filtros
                                        </Button>
                                    </div>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="flex gap-4">
                                        <div className="flex-1">
                                            <Input
                                                placeholder="Buscar por código de factura..."
                                                value={searchTerm}
                                                onChange={(e) => setSearchTerm(e.target.value)}
                                                onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                                            />
                                        </div>
                                        <Button onClick={handleSearch}>
                                            <Search className="h-4 w-4 mr-2" />
                                            Buscar
                                        </Button>
                                    </div>

                                    {showFilters && (
                                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                                    Almacén
                                                </label>
                                                <Select value={warehouseFilter} onValueChange={handleWarehouseChange}>
                                                    <SelectTrigger>
                                                        <SelectValue placeholder="Seleccionar almacén" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="all">Todos los almacenes</SelectItem>
                                                        {/* TODO: Agregar warehouses dinámicamente */}
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                            <div className="flex items-end">
                                                <Button variant="outline" onClick={clearFilters}>
                                                    Limpiar Filtros
                                                </Button>
                                            </div>
                                        </div>
                                    )}

                                    {hasFilters && (
                                        <div className="flex flex-wrap gap-2">
                                            {filters.search && (
                                                <Badge variant="secondary">
                                                    Búsqueda: {filters.search}
                                                </Badge>
                                            )}
                                            {filters.warehouse_id && (
                                                <Badge variant="secondary">
                                                    Almacén ID: {filters.warehouse_id}
                                                </Badge>
                                            )}
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            {/* Tabla de Facturas */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>
                                        Facturas ({pagination.from}-{pagination.to} de {pagination.total})
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="rounded-md border">
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>Código</TableHead>
                                                    <TableHead>Almacén</TableHead>
                                                    <TableHead>Items</TableHead>
                                                    <TableHead>Total</TableHead>
                                                    <TableHead>Fecha</TableHead>
                                                    <TableHead className="w-[100px]">Acciones</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {invoices.data.length === 0 ? (
                                                    <TableRow>
                                                        <TableCell colSpan={6} className="text-center py-8">
                                                            <div className="flex flex-col items-center gap-2">
                                                                <Receipt className="h-8 w-8 text-gray-400" />
                                                                <p className="text-gray-500">No se encontraron facturas</p>
                                                                {hasFilters && (
                                                                    <Button variant="outline" size="sm" onClick={clearFilters}>
                                                                        Limpiar filtros
                                                                    </Button>
                                                                )}
                                                            </div>
                                                        </TableCell>
                                                    </TableRow>
                                                ) : (
                                                    invoices.data.map((invoice) => (
                                                        <TableRow key={invoice.id}>
                                                            <TableCell className="font-medium">
                                                                {invoice.code}
                                                            </TableCell>
                                                            <TableCell>
                                                                <div>
                                                                    <div className="font-medium">{invoice.warehouse.name}</div>
                                                                    <div className="text-sm text-gray-500">{invoice.warehouse.code}</div>
                                                                </div>
                                                            </TableCell>
                                                            <TableCell>
                                                                <Badge variant="outline">
                                                                    {invoice.items_count} items
                                                                </Badge>
                                                            </TableCell>
                                                            <TableCell className="font-medium">
                                                                {formatCurrency(invoice.total_amount)}
                                                            </TableCell>
                                                            <TableCell>
                                                                {formatDate(invoice.created_at)}
                                                            </TableCell>
                                                            <TableCell>
                                                                <div className="flex items-center justify-end space-x-2">
                                                                    <Link href={`/invoices/${invoice.id}`}>
                                                                        <Button variant="ghost" size="sm">
                                                                            <Eye className="h-4 w-4" />
                                                                        </Button>
                                                                    </Link>
                                                                    <Link href={`/invoices/${invoice.id}/edit`}>
                                                                        <Button variant="ghost" size="sm">
                                                                            <Edit className="h-4 w-4" />
                                                                        </Button>
                                                                    </Link>
                                                                </div>
                                                            </TableCell>
                                                        </TableRow>
                                                    ))
                                                )}
                                            </TableBody>
                                        </Table>
                                    </div>
                                </CardContent>
                            </Card>
            </div>
        </AuthenticatedLayout>
    );
}
