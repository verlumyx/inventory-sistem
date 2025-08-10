import { Head, Link, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, Wrench, CheckCircle } from 'lucide-react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';

interface Item { id:number; code:string; name:string; unit:string; display_name:string }
interface AdjustmentItem { id:number; item_id:number; amount:number; item: Item }
interface Warehouse { id:number; code:string; name:string; display_name:string }
interface Adjustment {
  id:number;
  code:string;
  description:string|null;
  warehouse_id:number;
  warehouse?: Warehouse;
  type:string;
  type_text:string;
  status:number;
  status_text:string;
  is_pending: boolean;
  can_edit: boolean;
  items: AdjustmentItem[];
  created_at:string;
  updated_at:string
}

interface Props { adjustment: Adjustment }

export default function Show({ adjustment }: Props) {
  const { flash } = usePage().props as any;

  const handleMarkAsApplied = () => {
    router.patch(`/adjustments/${adjustment.id}/mark-as-applied`, {}, {
      onSuccess: () => {
        // El redirect se maneja en el controlador
      },
    });
  };

  return (
    <AuthenticatedLayout breadcrumbs={[{ title: 'Panel de Control', href: '/dashboard' }, { title: 'Ajustes', href: '/adjustments' }, { title: adjustment.code, href: `/adjustments/${adjustment.id}`, current: true }]}>
      <Head title={adjustment.code} />

      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-bold">Ajuste {adjustment.code}</h1>
          <p className="text-sm text-muted-foreground">{adjustment.description ?? 'Sin descripción'}</p>
        </div>
        <div className="flex gap-2">
          <Button asChild variant="outline">
            <Link href="/adjustments">
              <ArrowLeft className="h-4 w-4 mr-2" /> Volver
            </Link>
          </Button>

          {/* Botones de estado */}
          {adjustment.is_pending && (
            <Button onClick={handleMarkAsApplied} className="bg-green-600 hover:bg-green-700">
              <CheckCircle className="h-4 w-4 mr-2" />
              Aplicar al Inventario
            </Button>
          )}

          {/* Botón de editar solo si puede editarse */}
          {adjustment.can_edit && (
            <Button asChild>
              <Link href={`/adjustments/${adjustment.id}/edit`}>
                <Edit className="h-4 w-4 mr-2" /> Editar
              </Link>
            </Button>
          )}
        </div>
      </div>

      {/* Flash Messages */}
      {flash?.success && (
        <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md flex items-center gap-2 mb-6">
          <CheckCircle className="h-4 w-4" />
          {flash.success}
        </div>
      )}

      {flash?.error && (
        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md mb-6">
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
        <div className="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-md flex items-center gap-2 mb-6">
          <svg className="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
            <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
          </svg>
          {flash.info}
        </div>
      )}

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2"><Wrench className="h-5 w-5" /> Información</CardTitle>
            <CardDescription>Detalles del ajuste</CardDescription>
          </CardHeader>
          <CardContent className="space-y-2">
            <div><strong>Código:</strong> <span className="font-mono">{adjustment.code}</span></div>
            <div><strong>Tipo:</strong> <Badge variant={adjustment.type === 'negative' ? 'destructive' : 'default'}>{adjustment.type_text}</Badge></div>
            <div><strong>Bodega:</strong> {adjustment.warehouse?.display_name || 'Sin bodega asignada'}</div>
            <div><strong>Estado:</strong> <Badge variant={adjustment.status === 1 ? 'default' : 'secondary'}>{adjustment.status_text}</Badge></div>
            <div><strong>Creado:</strong> {new Date(adjustment.created_at).toLocaleString()}</div>
            <div><strong>Actualizado:</strong> {new Date(adjustment.updated_at).toLocaleString()}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Descripción</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            <div>
              <strong>Motivo:</strong> {adjustment.description ?? 'Sin descripción'}
            </div>
            <div>
              <strong>Efecto en inventario:</strong>{' '}
              <span className={adjustment.type === 'positive' ? 'text-green-600' : 'text-red-600'}>
                {adjustment.type === 'positive'
                  ? 'Aumentará las cantidades en el almacén'
                  : 'Disminuirá las cantidades en el almacén'
                }
              </span>
            </div>
          </CardContent>
        </Card>
      </div>

      <Card className="mt-4">
        <CardHeader>
          <CardTitle>Items del Ajuste</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Item</TableHead>
                  <TableHead>Código</TableHead>
                  <TableHead className="text-right">Cantidad</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {adjustment.items.length === 0 ? (
                  <TableRow><TableCell colSpan={3} className="text-center py-8 text-muted-foreground">Sin items</TableCell></TableRow>
                ) : (
                  adjustment.items.map((it) => (
                    <TableRow key={it.id}>
                      <TableCell>{it.item?.name ?? it.item_id}</TableCell>
                      <TableCell className="font-mono">{it.item?.code ?? '—'}</TableCell>
                      <TableCell className="text-right">{it.amount.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>
    </AuthenticatedLayout>
  );
}

