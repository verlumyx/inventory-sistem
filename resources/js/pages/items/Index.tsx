import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Plus, Search, Filter, Eye, Edit, QrCode } from 'lucide-react';
import type { BreadcrumbItem } from '@/types';

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
    total_stock: number;
    created_at: string;
    updated_at: string;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    has_more_pages: boolean;
}

interface Props {
    items: Item[];
    pagination: Pagination;
    filters: {
        search?: string;
        status?: boolean;
        name?: string;
        code?: string;
        qr_code?: string;
        unit?: string;
        min_price?: number;
        max_price?: number;
    };
}

export default function Index({ items, pagination, filters }: Props) {
    const { errors, flash } = usePage().props as any;
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState<string>(() => {
        if (filters.status !== undefined && filters.status !== null) {
            return filters.status ? 'true' : 'false';
        }
        return 'all';
    });
    const [showFilters, setShowFilters] = useState(false);

    // Debug log to see what filters we're receiving
    console.log('Received filters:', filters);

    const applyFilters = () => {
        const params: any = {};
        
        if (searchTerm) params.search = searchTerm;
        if (statusFilter !== 'all') params.status = statusFilter === 'true';
        
        router.get('/items', params, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleSearch = () => {
        applyFilters();
    };

    const handleStatusChange = (value: string) => {
        setStatusFilter(value);
        // Apply filters immediately when status changes
        const params: any = {};
        
        if (searchTerm) params.search = searchTerm;
        if (value !== 'all') params.status = value === 'true';
        
        console.log('Applying filters:', params); // Debug log
        
        router.get('/items', params, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const clearFilters = () => {
        setSearchTerm('');
        setStatusFilter('all');
        router.get('/items', {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handlePageChange = (page: number) => {
        const params: any = { page };
        
        if (searchTerm) params.search = searchTerm;
        if (statusFilter !== 'all') params.status = statusFilter === 'true';
        
        router.get('/items', params, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Panel de Control', href: '/dashboard' },
        { title: 'Items', href: '/items' },
    ];

    return (
        <AuthenticatedLayout breadcrumbs={breadcrumbs}>
            <Head title="Items" />

            <div className="p-6 space-y-6">

                {/* Header */}
                <div className="flex justify-between items-start">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Artículos</h1>
                        <p className="text-gray-600 mt-1">
                            Gestiona los almacenes del sistema de inventario
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/items/create">
                            <Plus className="h-4 w-4 mr-2" />
                            Nuevo Artículo
                        </Link>
                    </Button>
                </div>

                {/* Flash Messages */}
                {flash?.success && (
                    <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                        {flash.success}
                    </div>
                )}

                {flash?.error && (
                    <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                        {flash.error}
                    </div>
                )}

                {/* Filters */}
                <Card className="shadow-sm">
                    <CardHeader className="pb-3">
                        <div className="flex items-center justify-between">
                            <CardTitle className="text-lg">Filtros</CardTitle>
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
                                    placeholder="Buscar por nombre, código, descripción o QR..."
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
                                        Estado
                                    </label>
                                    <Select value={statusFilter} onValueChange={handleStatusChange}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Seleccionar estado" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">Todos</SelectItem>
                                            <SelectItem value="true">Activos</SelectItem>
                                            <SelectItem value="false">Inactivos</SelectItem>
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
                    </CardContent>
                </Card>

                {/* Results */}
                <Card className="shadow-sm">
                    <CardHeader>
                        <CardTitle>
                            Resultados
                            <span className="text-sm font-normal text-gray-500 ml-2">
                                Mostrando {pagination.from || 0} - {pagination.to || 0} de {pagination.total} items
                            </span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {items.length > 0 ? (
                            <>
                                <div className="rounded-md border overflow-hidden">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead className="py-4">Código</TableHead>
                                                <TableHead className="py-4">Nombre</TableHead>
                                                <TableHead className="py-4">Precio</TableHead>
                                                <TableHead className="py-4">Unidad</TableHead>
                                                <TableHead className="py-4">Disponible</TableHead>
                                                <TableHead className="py-4">Codigo de barra</TableHead>
                                                <TableHead className="py-4">Estado</TableHead>
                                                <TableHead className="py-4">Fecha Creación</TableHead>
                                                <TableHead className="text-right py-4">Acciones</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {items.map((item) => (
                                                <TableRow key={item.id}>
                                                    <TableCell className="font-mono text-sm py-4">
                                                        {item.code}
                                                    </TableCell>
                                                    <TableCell className="font-medium py-4">
                                                        {item.name}
                                                    </TableCell>
                                                    <TableCell className="py-4">
                                                        {item.price ? (
                                                            <span className="font-medium">
                                                                ${item.price.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                            </span>
                                                        ) : (
                                                            <span className="text-gray-400">-</span>
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="py-4">
                                                        {item.unit || <span className="text-gray-400">-</span>}
                                                    </TableCell>
                                                    <TableCell className="py-4">
                                                        <div className="flex items-center gap-1">
                                                            <span className={`font-medium ${item.total_stock > 0 ? 'text-green-700' : 'text-red-600'}`}>
                                                                {item.total_stock.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                            </span>
                                                            <span className="text-gray-500 text-sm">
                                                                {item.unit || 'unidades'}
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell className="py-4">
                                                        {item.qr_code ? (
                                                            <div className="flex items-center gap-2">
                                                                <QrCode className="h-4 w-4 text-gray-500" />
                                                                <span className="font-mono text-xs">
                                                                    {item.qr_code}
                                                                </span>
                                                            </div>
                                                        ) : (
                                                            <span className="text-gray-400">-</span>
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="py-4">
                                                        <Badge 
                                                            variant={item.status ? 'default' : 'secondary'}
                                                        >
                                                            {item.status_text}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell className="py-4">
                                                        {new Date(item.created_at).toLocaleDateString('es-ES')}
                                                    </TableCell>
                                                    <TableCell className="text-right py-4">
                                                        <div className="flex items-center justify-end space-x-2">
                                                            <Link href={`/items/${item.id}`}>
                                                                <Button variant="ghost" size="sm">
                                                                    <Eye className="h-4 w-4" />
                                                                </Button>
                                                            </Link>
                                                            <Link href={`/items/${item.id}/edit`}>
                                                                <Button variant="ghost" size="sm">
                                                                    <Edit className="h-4 w-4" />
                                                                </Button>
                                                            </Link>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>

                                {/* Pagination */}
                                {pagination.last_page > 1 && (
                                    <div className="flex items-center justify-between mt-6">
                                        <div className="text-sm text-gray-700">
                                            Página {pagination.current_page} de {pagination.last_page}
                                        </div>
                                        <div className="flex space-x-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => handlePageChange(pagination.current_page - 1)}
                                                disabled={pagination.current_page === 1}
                                            >
                                                Anterior
                                            </Button>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => handlePageChange(pagination.current_page + 1)}
                                                disabled={!pagination.has_more_pages}
                                            >
                                                Siguiente
                                            </Button>
                                        </div>
                                    </div>
                                )}
                            </>
                        ) : (
                            <div className="text-center py-12">
                                <div className="text-gray-500 mb-4">
                                    No se encontraron items
                                </div>
                                <Button asChild>
                                    <Link href="/items/create">
                                        <Plus className="h-4 w-4 mr-2" />
                                        Crear Primer Item
                                    </Link>
                                </Button>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
