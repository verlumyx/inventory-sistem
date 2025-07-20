import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Search, Filter, Eye, Edit, MoreHorizontal, Package } from 'lucide-react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

interface Entry {
    id: number;
    code: string;
    name: string;
    description?: string;
    status: number; // 0 = Por recibir, 1 = Recibido
    status_text: string;
    created_at: string;
    updated_at: string;
}

interface PaginationData {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Props {
    entries: {
        data: Entry[];
        links: any[];
        meta?: any;
    };
    filters: {
        search?: string;
        status?: boolean;
        name?: string;
        code?: string;
    };
    pagination: PaginationData;
}

export default function Index({ entries, filters, pagination }: Props) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [showFilters, setShowFilters] = useState(false);
    const [statusFilter, setStatusFilter] = useState(
        filters.status !== undefined ? (filters.status ? 'true' : 'false') : 'all'
    );

    const handleSearch = (e?: React.FormEvent) => {
        if (e) e.preventDefault();

        const params: any = {};

        if (searchTerm) params.search = searchTerm;
        if (statusFilter !== 'all') params.status = statusFilter;

        router.get(route('entries.index'), params, {
            preserveState: true,
            replace: true
        });
    };

    const handleStatusChange = (value: string) => {
        setStatusFilter(value);

        const params: any = {};
        if (searchTerm) params.search = searchTerm;
        if (value !== 'all') params.status = value;

        router.get(route('entries.index'), params, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setSearchTerm('');
        setStatusFilter('all');
        router.get(route('entries.index'), {}, {
            preserveState: true,
            replace: true
        });
    };

    const hasFilters = Object.keys(filters).some(key => filters[key as keyof typeof filters]);

    return (
        <AuthenticatedLayout>
            <Head title="Entradas" />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Entradas</h1>
                        <p className="text-muted-foreground">
                            Gestiona las entradas de inventario y sus items
                        </p>
                    </div>
                    <Link href={route('entries.create')}>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Nueva Entrada
                        </Button>
                    </Link>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Entradas</CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{pagination.total}</div>
                            <p className="text-xs text-muted-foreground">
                                Entradas registradas
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Recibidas</CardTitle>
                            <Package className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">
                                {entries.data.filter(entry => entry.status).length}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Entradas recibidas
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Por recibir</CardTitle>
                            <Package className="h-4 w-4 text-red-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">
                                {entries.data.filter(entry => !entry.status).length}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Entradas por recibir
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Esta Página</CardTitle>
                            <Package className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{entries.data.length}</div>
                            <p className="text-xs text-muted-foreground">
                                {pagination.from}-{pagination.to} de {pagination.total}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Search and Filters */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>Buscar Entradas</CardTitle>
                                <CardDescription>
                                    Encuentra entradas por nombre, código o descripción
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
                                    placeholder="Buscar por nombre, código o descripción..."
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
                                            <SelectItem value="false">Por recibir</SelectItem>
                                            <SelectItem value="true">Recibidas</SelectItem>
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

                {/* Entries Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Lista de Entradas</CardTitle>
                        <CardDescription>
                            {pagination.total > 0 
                                ? `Mostrando ${pagination.from} a ${pagination.to} de ${pagination.total} entradas`
                                : 'No se encontraron entradas'
                            }
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {entries.data.length > 0 ? (
                            <div className="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead className="py-4">Código</TableHead>
                                            <TableHead className="py-4">Nombre</TableHead>
                                            <TableHead className="py-4">Descripción</TableHead>
                                            <TableHead className="py-4">Estado</TableHead>
                                            <TableHead className="py-4">Fecha Creación</TableHead>
                                            <TableHead className="text-right py-4">Acciones</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {entries.data.map((entry) => (
                                            <TableRow key={entry.id}>
                                                <TableCell className="font-mono py-4">
                                                    {entry.code}
                                                </TableCell>
                                                <TableCell className="font-medium py-4">
                                                    {entry.name}
                                                </TableCell>
                                                <TableCell className="max-w-xs truncate py-4">
                                                    {entry.description || '-'}
                                                </TableCell>
                                                <TableCell className="py-4">
                                                    <Badge
                                                        variant={entry.status_text === 'Recibido' ? "default" : "secondary"}
                                                        className={entry.status_text === 'Recibido' ? "bg-green-100 text-green-800" : "bg-yellow-100 text-yellow-800"}
                                                    >
                                                        {entry.status_text}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="py-4">
                                                    {new Date(entry.created_at).toLocaleDateString('es-ES')}
                                                </TableCell>
                                                <TableCell className="text-right py-4">
                                                    <div className="flex items-center justify-end space-x-2">
                                                        <Link href={`/entries/${entry.id}`}>
                                                            <Button variant="ghost" size="sm">
                                                                <Eye className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                        {entry.status === 0 && (
                                                            <Link href={`/entries/${entry.id}/edit`}>
                                                                <Button variant="ghost" size="sm">
                                                                    <Edit className="h-4 w-4" />
                                                                </Button>
                                                            </Link>
                                                        )}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        ) : (
                            <div className="text-center py-12">
                                <Package className="mx-auto h-12 w-12 text-muted-foreground" />
                                <h3 className="mt-4 text-lg font-semibold">No hay entradas</h3>
                                <p className="mt-2 text-muted-foreground">
                                    {hasFilters 
                                        ? 'No se encontraron entradas con los filtros aplicados.'
                                        : 'Comienza creando tu primera entrada.'
                                    }
                                </p>
                                {!hasFilters && (
                                    <Link href={route('entries.create')} className="mt-4 inline-block">
                                        <Button>
                                            <Plus className="mr-2 h-4 w-4" />
                                            Nueva Entrada
                                        </Button>
                                    </Link>
                                )}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Pagination */}
                {pagination.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <div className="text-sm text-muted-foreground">
                            Página {pagination.current_page} de {pagination.last_page}
                        </div>
                        <div className="flex items-center space-x-2">
                            {entries.links.map((link, index) => (
                                <Button
                                    key={index}
                                    variant={link.active ? "default" : "outline"}
                                    size="sm"
                                    disabled={!link.url}
                                    onClick={() => link.url && router.visit(link.url)}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
