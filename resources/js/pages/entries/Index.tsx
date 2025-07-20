import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Search, Filter, Eye, Edit, MoreHorizontal, Package } from 'lucide-react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
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

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('entries.index'), { 
            search: searchTerm,
            ...filters 
        }, { 
            preserveState: true,
            replace: true 
        });
    };

    const clearFilters = () => {
        setSearchTerm('');
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
                            <CardTitle className="text-sm font-medium">Activas</CardTitle>
                            <Package className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">
                                {entries.data.filter(entry => entry.status).length}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Entradas activas
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Inactivas</CardTitle>
                            <Package className="h-4 w-4 text-red-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">
                                {entries.data.filter(entry => !entry.status).length}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Entradas inactivas
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
                                variant="outline"
                                size="sm"
                                onClick={() => setShowFilters(!showFilters)}
                            >
                                <Filter className="mr-2 h-4 w-4" />
                                Filtros
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <form onSubmit={handleSearch} className="flex gap-2">
                            <div className="relative flex-1">
                                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    placeholder="Buscar por nombre, código o descripción..."
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    className="pl-10"
                                />
                            </div>
                            <Button type="submit">Buscar</Button>
                            {hasFilters && (
                                <Button type="button" variant="outline" onClick={clearFilters}>
                                    Limpiar
                                </Button>
                            )}
                        </form>

                        {hasFilters && (
                            <div className="flex flex-wrap gap-2">
                                {filters.search && (
                                    <Badge variant="secondary">
                                        Búsqueda: {filters.search}
                                    </Badge>
                                )}
                                {filters.status !== undefined && (
                                    <Badge variant="secondary">
                                        Estado: {filters.status ? 'Activo' : 'Inactivo'}
                                    </Badge>
                                )}
                                {filters.name && (
                                    <Badge variant="secondary">
                                        Nombre: {filters.name}
                                    </Badge>
                                )}
                                {filters.code && (
                                    <Badge variant="secondary">
                                        Código: {filters.code}
                                    </Badge>
                                )}
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
