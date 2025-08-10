import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

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
  const list = Array.isArray(transfers) ? transfers : (transfers.data || []);
  const [searchTerm, setSearchTerm] = React.useState<string>(filters.search || '');
  const [statusFilter, setStatusFilter] = React.useState<string>(filters.status !== undefined ? String(filters.status) : 'all');
  const handleSearch = (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    const params: any = {};
    if (searchTerm) params.search = searchTerm;
    if (statusFilter !== 'all') params.status = parseInt(statusFilter);
    router.get('/transfers', params, { preserveState: true, replace: true });
  };


interface Props {
  transfers: { data: TransferListItem[] } | TransferListItem[];
  filters: { search?: string; status?: number };
  pagination: { current_page: number; last_page: number; per_page: number; total: number };
}

export default function Index({ transfers, filters, pagination }: Props) {
  const breadcrumbs = [
    { title: 'Panel de Control', href: '/dashboard' },
    { title: 'Traslados', href: '/transfers', current: true },
  ];

  return (
        <form onSubmit={handleSearch} className="mb-4 flex items-center gap-2">
          <Input className="flex-1" placeholder="Buscar por código o descripción" value={searchTerm} onChange={(e)=>setSearchTerm(e.target.value)} />
          <Select value={statusFilter} onValueChange={(v)=>setStatusFilter(v)}>
            <SelectTrigger className="w-[180px]"><SelectValue placeholder="Estado" /></SelectTrigger>
            <SelectContent>
              <SelectItem value="all">Todos</SelectItem>
              <SelectItem value="0">Pendiente</SelectItem>
              <SelectItem value="1">Completado</SelectItem>
            </SelectContent>
          </Select>
          <Button type="submit" variant="secondary">Buscar</Button>
        </form>

    <AuthenticatedLayout breadcrumbs={breadcrumbs}>
      <Head title="Traslados" />

      <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div className="flex justify-between items-center mb-4">
          <h1 className="text-xl font-semibold">Traslados</h1>
          <Button asChild>
            <Link href="/transfers/create">Nuevo Traslado</Link>
          </Button>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Listado</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="overflow-x-auto">
              <table className="min-w-full text-sm">
                <thead>
                  <tr className="text-left">
                    <th className="p-2">Código</th>
                    <th className="p-2">Descripción</th>
                    <th className="p-2">Origen</th>
                    <th className="p-2">Destino</th>
                    <th className="p-2">Estado</th>
                    <th className="p-2">Creado</th>
                    <th className="p-2 text-right">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {(Array.isArray(transfers) ? transfers : transfers.data || []).map((t) => (
                    <tr key={t.id} className="border-t">
                      <td className="p-2 font-mono">{t.code}</td>
                      <td className="p-2">{t.description || '-'}</td>
                      <td className="p-2">{t.source || '-'}</td>
                      <td className="p-2">{t.destination || '-'}</td>
                      <td className="p-2">
                        <span className={`px-2 py-1 rounded text-xs ${t.status === 1 ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'}`}>{t.status_text}</span>
                      </td>
                      <td className="p-2">{new Date(t.created_at).toLocaleString()}</td>
                      <td className="p-2 text-right">
                        <Link href={`/transfers/${t.id}`} className="text-blue-600 hover:underline mr-3">Ver</Link>
                        <Link href={`/transfers/${t.id}/edit`} className="text-blue-600 hover:underline">Editar</Link>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppSidebarLayout>
  );
}

