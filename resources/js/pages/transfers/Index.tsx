import React from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus, Search, Filter, Eye, Edit } from 'lucide-react';
import type { BreadcrumbItem } from '@/types';

interface TransferListItem {
  id: number;
  code: string;
  description?: string;
  status: number;
  status_text: string;
  source?: string | null;
  destination?: string | null;
  created_at: string;
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
  transfers: { data: TransferListItem[] } | TransferListItem[];
  filters: { search?: string; status?: number };
  pagination: Pagination;
}

export default function Index({ transfers, filters, pagination }: Props) {
  const { flash } = usePage().props as any;
  const [searchTerm, setSearchTerm] = React.useState<string>(filters.search || '');
  const [statusFilter, setStatusFilter] = React.useState<string>(filters.status !== undefined ? String(filters.status) : 'all');
  const [showFilters, setShowFilters] = React.useState(false);

  const list = Array.isArray(transfers) ? transfers : (transfers.data || []);

  const handleSearch = () => {
    const params: any = {};
    if (searchTerm) params.search = searchTerm;
    if (statusFilter !== 'all') params.status = parseInt(statusFilter);
    router.get('/transfers', params, {
      preserveState: true,
      preserveScroll: true
    });
  };

  const handleStatusChange = (value: string) => {
    setStatusFilter(value);
    const params: any = {};
    if (searchTerm) params.search = searchTerm;
    if (value !== 'all') params.status = parseInt(value);

    router.get('/transfers', params, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const clearFilters = () => {
    setSearchTerm('');
    setStatusFilter('all');
    router.get('/transfers', {}, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handlePageChange = (page: number) => {
    const params: any = { page };
    if (searchTerm) params.search = searchTerm;
    if (statusFilter !== 'all') params.status = parseInt(statusFilter);

    router.get('/transfers', params, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Panel de Control', href: '/dashboard' },
    { title: 'Traslados', href: '/transfers' },
  ];

  return (
    <AuthenticatedLayout breadcrumbs={breadcrumbs}>
      <Head title="Traslados" />

      <div className="p-6 space-y-6">
        {/* Header */}
        <div className="flex justify-between items-start">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Traslados</h1>
            <p className="text-gray-600 mt-1">
              Gestiona los traslados entre almacenes del sistema de inventario
            </p>
          </div>
          <div className="flex gap-2">
            <Button asChild>
              <Link href="/transfers/create">
                <Plus className="h-4 w-4 mr-2" />
                Nuevo Traslado
              </Link>
            </Button>
          </div>
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
                  placeholder="Buscar por código o descripción..."
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
                      <SelectItem value="0">Pendiente</SelectItem>
                      <SelectItem value="1">Completado</SelectItem>
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
                Mostrando {pagination.from || 0} - {pagination.to || 0} de {pagination.total} traslados
              </span>
            </CardTitle>
          </CardHeader>
          <CardContent>
            {list.length > 0 ? (
              <>
                <div className="rounded-md border overflow-hidden">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead className="py-4">Código</TableHead>
                        <TableHead className="py-4">Descripción</TableHead>
                        <TableHead className="py-4">Origen</TableHead>
                        <TableHead className="py-4">Destino</TableHead>
                        <TableHead className="py-4">Estado</TableHead>
                        <TableHead className="py-4">Fecha Creación</TableHead>
                        <TableHead className="text-right py-4">Acciones</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {list.map((transfer) => (
                        <TableRow key={transfer.id}>
                          <TableCell className="font-mono text-sm py-4">
                            {transfer.code}
                          </TableCell>
                          <TableCell className="py-4">
                            {transfer.description || <span className="text-gray-400">-</span>}
                          </TableCell>
                          <TableCell className="py-4">
                            {transfer.source || <span className="text-gray-400">-</span>}
                          </TableCell>
                          <TableCell className="py-4">
                            {transfer.destination || <span className="text-gray-400">-</span>}
                          </TableCell>
                          <TableCell className="py-4">
                            <Badge
                              variant={transfer.status === 1 ? 'default' : 'secondary'}
                            >
                              {transfer.status_text}
                            </Badge>
                          </TableCell>
                          <TableCell className="py-4">
                            {new Date(transfer.created_at).toLocaleDateString('es-ES')}
                          </TableCell>
                          <TableCell className="text-right py-4">
                            <div className="flex items-center justify-end space-x-2">
                              <Link href={`/transfers/${transfer.id}`}>
                                <Button variant="ghost" size="sm">
                                  <Eye className="h-4 w-4" />
                                </Button>
                              </Link>
                              <Link href={`/transfers/${transfer.id}/edit`}>
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
                  No se encontraron traslados
                </div>
                <Button asChild>
                  <Link href="/transfers/create">
                    <Plus className="h-4 w-4 mr-2" />
                    Crear Primer Traslado
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

