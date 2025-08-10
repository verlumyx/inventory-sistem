import { Head, Link } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export default function Show({ transfer }: any) {
  const breadcrumbs = [
    { title: 'Panel de Control', href: '/dashboard' },
    { title: 'Traslados', href: '/transfers' },
    { title: transfer.code, href: `/transfers/${transfer.id}`, current: true },
  ];

  return (
    <AppSidebarLayout breadcrumbs={breadcrumbs}>
      <Head title={`Traslado ${transfer.code}`} />

      <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
        <div className="flex justify-between items-center">
          <h1 className="text-xl font-semibold">Traslado {transfer.code}</h1>
          <div className="flex gap-2">
            <Button asChild variant="secondary"><Link href={`/transfers/${transfer.id}/edit`}>Editar</Link></Button>
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Información</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            <div><span className="font-medium">Código:</span> {transfer.code}</div>
            <div><span className="font-medium">Descripción:</span> {transfer.description || '-'}</div>
            <div><span className="font-medium">Origen:</span> {transfer.source?.display_name || '-'}</div>
            <div><span className="font-medium">Destino:</span> {transfer.destination?.display_name || '-'}</div>
            <div><span className="font-medium">Estado:</span> <Badge variant={transfer.status === 1 ? 'default' : 'secondary'}>{transfer.status_text}</Badge></div>
            <div><span className="font-medium">Creado:</span> {new Date(transfer.created_at).toLocaleString()}</div>
            <div><span className="font-medium">Actualizado:</span> {new Date(transfer.updated_at).toLocaleString()}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Items</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="overflow-x-auto">
              <table className="min-w-full text-sm">
                <thead><tr><th className="p-2 text-left">Item</th><th className="p-2 text-left">Cantidad</th></tr></thead>
                <tbody>
                  {transfer.items.map((it: any) => (
                    <tr key={it.id} className="border-t">
                      <td className="p-2">{it.item.display_name}</td>
                      <td className="p-2">{it.formatted_amount}</td>
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

