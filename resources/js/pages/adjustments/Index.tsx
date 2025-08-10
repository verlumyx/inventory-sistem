import { Head, Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Plus, Edit, Eye, Wrench, Search, CheckCircle } from 'lucide-react';
import { useState } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';

interface Warehouse { id:number; code:string; name:string; display_name:string }
interface Adjustment {
  id: number;
  code: string;
  description: string | null;
  warehouse_id: number;
  warehouse?: Warehouse;
  type: string;
  type_text: string;
  status: number;
  status_text: string;
  is_pending: boolean;
  is_applied: boolean;
  can_edit: boolean;
  created_at: string;
  updated_at: string;
  display_name: string;
}

interface Props {
  adjustments: { data: Adjustment[] } | Adjustment[];
  filters: Record<string, any>;
  pagination: { current_page: number; per_page: number; total: number; last_page: number };
}

export default function Index({ adjustments, filters, pagination }: Props) {
  const { flash } = usePage().props as any;
  const [search, setSearch] = useState(filters?.search ?? '');

  // Normaliza el listado si viene paginado ({data: []}) o como arreglo directo []
  const list: Adjustment[] = Array.isArray((adjustments as any)?.data)
    ? ((adjustments as any).data as Adjustment[])
    : (Array.isArray(adjustments) ? (adjustments as Adjustment[]) : []);

  const handleSearch = (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    const params: any = {};
    if (search) params.search = search;
    router.get(route('adjustments.index'), params, {
      preserveState: true,
      replace: true,
    });
  };

  const clearFilters = () => {
    setSearch('');
    router.get(route('adjustments.index'), {}, {
      preserveState: true,
      replace: true,
    });
  };

  const breadcrumbs = [
    { title: 'Panel de Control', href: '/dashboard' },
    { title: 'Ajustes', href: '/adjustments' },
  ];

  return (
    <AuthenticatedLayout breadcrumbs={breadcrumbs}>
      <Head title="Ajustes" />

      <div className="p-6 space-y-6">
        {/* Flash Messages */}
        {flash?.success && (
          <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md flex items-center gap-2">
            <CheckCircle className="h-4 w-4" />
            {flash.success}
          </div>
        )}

        {flash?.error && (
          <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
            <div className="flex items-start">
              <div className="flex-shrink-0">
                <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
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

        {flash?.info && (
          <div className="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-md flex items-center gap-2">
            <svg className="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
            </svg>
            {flash.info}
          </div>
        )}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Ajustes</h1>
            <p className="text-muted-foreground">Gestión de ajustes de inventario</p>
          </div>
          <div className="flex gap-2">
            <Button asChild>
              <Link href="/adjustments/create">
                <Plus className="mr-2 h-4 w-4" />
                Nuevo Ajuste
              </Link>
            </Button>
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Wrench className="h-5 w-5" /> Lista de Ajustes
            </CardTitle>
            <CardDescription>Filtra y navega por los ajustes</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="flex gap-4">
              <div className="flex-1">
                <input
                  placeholder="Buscar por código o descripción"
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                  className="pl-3 pr-3 py-2 border rounded-md w-full"
                />
              </div>
              <Button onClick={handleSearch}>
                <Search className="h-4 w-4 mr-2" />
                Buscar
              </Button>
            </div>

            <div className="rounded-md border">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Código</TableHead>
                    <TableHead>Descripción</TableHead>
                    <TableHead>Bodega</TableHead>
                    <TableHead>Tipo</TableHead>
                    <TableHead>Estado</TableHead>
                    <TableHead>Creado</TableHead>
                    <TableHead className="w-[120px] text-right">Acciones</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {list.length === 0 ? (
                    <TableRow>
                      <TableCell colSpan={6} className="text-center py-8 text-muted-foreground">
                        No se encontraron ajustes
                      </TableCell>
                    </TableRow>
                  ) : (
                    list.map((adj: Adjustment) => (
                      <TableRow key={adj.id}>
                        <TableCell className="font-medium">{adj.code}</TableCell>
                        <TableCell>{adj.description ?? '—'}</TableCell>
                        <TableCell>{adj.warehouse?.display_name || 'Sin bodega'}</TableCell>
                        <TableCell>
                          <Badge variant={adj.type === 'negative' ? 'destructive' : 'default'}>{adj.type_text}</Badge>
                        </TableCell>
                        <TableCell>
                          <Badge variant={adj.status === 1 ? 'default' : 'secondary'}>{adj.status_text}</Badge>
                        </TableCell>
                        <TableCell>{new Date(adj.created_at).toLocaleString()}</TableCell>
                        <TableCell className="text-right">
                          <div className="flex items-center justify-end space-x-2">
                            <Link href={`/adjustments/${adj.id}`}>
                              <Button variant="ghost" size="sm">
                                <Eye className="h-4 w-4" />
                              </Button>
                            </Link>
                            {adj.can_edit && (
                              <Link href={`/adjustments/${adj.id}/edit`}>
                                <Button variant="ghost" size="sm">
                                  <Edit className="h-4 w-4" />
                                </Button>
                              </Link>
                            )}
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

