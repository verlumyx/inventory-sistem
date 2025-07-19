import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Search, Plus, Eye, Edit, Filter, X } from 'lucide-react';
import { BreadcrumbItem } from '@/types';

interface Warehouse {
    id: number;
    code: string;
    name: string;
    description: string | null;
    status: boolean;
    status_text: string;
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

interface Filters {
    search?: string;
    status?: boolean;
    name?: string;
    code?: string;
}

interface Props {
    warehouses: Warehouse[];
    pagination: Pagination;
    filters: Filters;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Almacenes',
        href: '/warehouses',
    },
];

export default function Index({ warehouses, pagination, filters }: Props) {
    const { errors, flash } = usePage().props as any;
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState<string>(
        filters.status !== undefined ? (filters.status ? 'true' : 'false') : 'all'
    );
    const [showFilters, setShowFilters] = useState(false);

    const handleSearch = () => {
        const params: any = {};
        
        if (searchTerm) params.search = searchTerm;
        if (statusFilter !== 'all') params.status = statusFilter === 'true';
        
        router.get('/warehouses', params, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const clearFilters = () => {
        setSearchTerm('');
        setStatusFilter('all');
        router.get('/warehouses', {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handlePageChange = (page: number) => {
        const params: any = { page };
        
        if (searchTerm) params.search = searchTerm;
        if (statusFilter !== 'all') params.status = statusFilter === 'true';
        
        router.get('/warehouses', params, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout breadcrumbs={breadcrumbs}>
            <Head title="Almacenes" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            Almacenes
                        </h1>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            Gestiona los almacenes del sistema de inventario
                        </p>
                    </div>
                    <Link href="/warehouses/create">
                        <Button>
                            <Plus className="h-4 w-4 mr-2" />
                            Nuevo Almacén
                        </Button>
                    </Link>
                </div>

                {/* Flash Messages */}
                {flash?.success && (
                    <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                        {flash.success}
                    </div>
                )}

                {errors?.error && (
                    <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                        {errors.error}
                    </div>
                )}

                {/* Filters */}
                <Card>
                    <CardHeader className="pb-3">
                        <div className="flex items-center justify-between">
                            <CardTitle className="text-lg">Filtros</CardTitle>
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => setShowFilters(!showFilters)}
                            >
                                <Filter className="h-4 w-4 mr-2" />
                                {showFilters ? 'Ocultar' : 'Mostrar'} Filtros
                            </Button>
                        </div>
                    </CardHeader>
                    
                    {showFilters && (
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <Label htmlFor="search">Buscar</Label>
                                    <div className="relative">
                                        <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                        <Input
                                            id="search"
                                            placeholder="Buscar por nombre, código o descripción..."
                                            value={searchTerm}
                                            onChange={(e) => setSearchTerm(e.target.value)}
                                            className="pl-10"
                                            onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                                        />
                                    </div>
                                </div>
                                
                                <div>
                                    <Label htmlFor="status">Estado</Label>
                                    <Select value={statusFilter} onValueChange={setStatusFilter}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Seleccionar estado" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">Todos</SelectItem>
                                            <SelectItem value="true">Activo</SelectItem>
                                            <SelectItem value="false">Inactivo</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                
                                <div className="flex items-end space-x-2">
                                    <Button onClick={handleSearch} className="flex-1">
                                        Buscar
                                    </Button>
                                    <Button variant="outline" onClick={clearFilters}>
                                        <X className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    )}
                </Card>

                {/* Results */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>Resultados</CardTitle>
                                <CardDescription>
                                    {pagination.total > 0 
                                        ? `Mostrando ${pagination.from} - ${pagination.to} de ${pagination.total} almacenes`
                                        : 'No se encontraron almacenes'
                                    }
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    
                    <CardContent>
                        {warehouses.length > 0 ? (
                            <>
                                <div className="rounded-md border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Código</TableHead>
                                                <TableHead>Nombre</TableHead>
                                                <TableHead>Descripción</TableHead>
                                                <TableHead>Estado</TableHead>
                                                <TableHead>Fecha Creación</TableHead>
                                                <TableHead className="text-right">Acciones</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {warehouses.map((warehouse) => (
                                                <TableRow key={warehouse.id}>
                                                    <TableCell className="font-mono text-sm">
                                                        {warehouse.code}
                                                    </TableCell>
                                                    <TableCell className="font-medium">
                                                        {warehouse.name}
                                                    </TableCell>
                                                    <TableCell className="max-w-xs truncate">
                                                        {warehouse.description || '-'}
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge 
                                                            variant={warehouse.status ? 'default' : 'secondary'}
                                                        >
                                                            {warehouse.status_text}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell>
                                                        {new Date(warehouse.created_at).toLocaleDateString('es-ES')}
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <div className="flex items-center justify-end space-x-2">
                                                            <Link href={`/warehouses/${warehouse.id}`}>
                                                                <Button variant="ghost" size="sm">
                                                                    <Eye className="h-4 w-4" />
                                                                </Button>
                                                            </Link>
                                                            <Link href={`/warehouses/${warehouse.id}/edit`}>
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
                                    <div className="flex items-center justify-between mt-4">
                                        <div className="text-sm text-gray-600 dark:text-gray-400">
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
                            <div className="text-center py-8">
                                <p className="text-gray-500 dark:text-gray-400">
                                    No se encontraron almacenes con los filtros aplicados.
                                </p>
                                <Button variant="outline" onClick={clearFilters} className="mt-4">
                                    Limpiar Filtros
                                </Button>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
