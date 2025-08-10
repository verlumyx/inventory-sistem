import { Head, useForm, Link } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Plus } from 'lucide-react';
import { useState } from 'react';

interface Warehouse { id: number; code: string; name: string; display_name: string; default?: boolean }
interface Item { id: number; code: string; name: string; display_name: string; unit?: string }

interface TransferItem { item_id: number; amount: number }

interface Props { warehouses: Warehouse[]; items: Item[] }

export default function Create({ warehouses, items }: Props) {
  const { data, setData, post, processing, errors } = useForm({
    description: '',
    warehouse_id: '',
    warehouse_source_id: '',
    warehouse_destination_id: '',
    items: [] as TransferItem[],
  });

  const [selectedItem, setSelectedItem] = useState('');
  const [itemAmount, setItemAmount] = useState('');

  const addItem = () => {
    if (!selectedItem || !itemAmount) return;
    const itemId = parseInt(selectedItem);
    const amount = parseFloat(itemAmount);
    if (data.items.some((i: any) => i.item_id === itemId)) { alert('Este item ya está agregado'); return; }
    setData('items', [...(data.items as any[]), { item_id: itemId, amount }]);
    setSelectedItem(''); setItemAmount('');
  };

  const removeItem = (index: number) => {
    setData('items', (data.items as any[]).filter((_, i) => i !== index));
  };

  const breadcrumbs = [
    { title: 'Panel de Control', href: '/dashboard' },
    { title: 'Traslados', href: '/transfers' },
    { title: 'Crear', href: '/transfers/create', current: true },
  ];

  return (
    <AppSidebarLayout breadcrumbs={breadcrumbs}>
      <Head title="Nuevo Traslado" />

      <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <form onSubmit={(e) => { e.preventDefault(); post('/transfers'); }} className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Información General</CardTitle>
              <CardDescription>Completa los datos del traslado</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <Label htmlFor="description">Descripción</Label>
                <Input id="description" value={data.description as string} onChange={(e) => setData('description', e.target.value)} placeholder="Descripción del traslado (opcional)" />
              </div>

              <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                  <Label>Almacén Origen *</Label>
                  <Select value={data.warehouse_source_id as string} onValueChange={(v) => setData('warehouse_source_id', v)}>
                    <SelectTrigger><SelectValue placeholder="Seleccionar origen" /></SelectTrigger>
                    <SelectContent>
                      {warehouses.map(w => (<SelectItem key={w.id} value={w.id.toString()}>{w.display_name}</SelectItem>))}
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <Label>Almacén Destino *</Label>
                  <Select value={data.warehouse_destination_id as string} onValueChange={(v) => setData('warehouse_destination_id', v)}>
                    <SelectTrigger><SelectValue placeholder="Seleccionar destino" /></SelectTrigger>
                    <SelectContent>
                      {warehouses.map(w => (<SelectItem key={w.id} value={w.id.toString()}>{w.display_name}</SelectItem>))}
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <Label>Almacén (opcional)</Label>
                  <Select value={(data.warehouse_id as string) || ''} onValueChange={(v) => setData('warehouse_id', v)}>
                    <SelectTrigger><SelectValue placeholder="Seleccionar almacén" /></SelectTrigger>
                    <SelectContent>
                      <SelectItem value="">Sin almacén</SelectItem>
                      {warehouses.map(w => (<SelectItem key={w.id} value={w.id.toString()}>{w.display_name}</SelectItem>))}
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <Label>Estado</Label>
                  <Select value={(data as any).status || '0'} onValueChange={(v) => setData('status', v)}>
                    <SelectTrigger><SelectValue placeholder="Seleccionar estado" /></SelectTrigger>
                    <SelectContent>
                      <SelectItem value="0">Pendiente</SelectItem>
                      <SelectItem value="1">Completado</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2"><Plus className="h-5 w-5" />Agregar Items</CardTitle>
              <CardDescription>Agrega items a trasladar</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <Label>Item</Label>
                  <Select value={selectedItem} onValueChange={setSelectedItem}>
                    <SelectTrigger><SelectValue placeholder="Seleccionar item" /></SelectTrigger>
                    <SelectContent>
                      {items.map(i => (<SelectItem key={i.id} value={i.id.toString()}>{i.display_name}</SelectItem>))}
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <Label>Cantidad</Label>
                  <Input type="number" step="0.01" min="0.01" value={itemAmount} onChange={(e) => setItemAmount(e.target.value)} placeholder="0.00" />
                </div>
                <div className="flex items-end">
                  <Button type="button" onClick={addItem} className="w-full">Agregar</Button>
                </div>
              </div>

              <div>
                <h4 className="font-semibold mb-2">Items del Traslado ({(data.items as any[]).length})</h4>
                {(data.items as any[]).length === 0 ? (
                  <p className="text-sm text-gray-600">No hay items agregados aún</p>
                ) : (
                  <div className="overflow-x-auto">
                    <table className="min-w-full text-sm">
                      <thead><tr><th className="p-2 text-left">Item</th><th className="p-2 text-left">Cantidad</th><th className="p-2 text-right">Acciones</th></tr></thead>
                      <tbody>
                        {(data.items as any[]).map((it: any, index: number) => {
                          const item = items.find(i => i.id === it.item_id);
                          return (
                            <tr key={index} className="border-t">
                              <td className="p-2">{item?.display_name || it.item_id}</td>
                              <td className="p-2">{it.amount}</td>
                              <td className="p-2 text-right">
                                <Button type="button" variant="secondary" onClick={() => removeItem(index)}>Quitar</Button>
                              </td>
                            </tr>
                          );
                        })}
                      </tbody>
                    </table>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>

          <div className="flex gap-2 justify-end">
            <Button type="button" variant="secondary" asChild><Link href="/transfers">Cancelar</Link></Button>
            <Button type="submit" disabled={processing}>Guardar</Button>
          </div>
        </form>
      </div>
    </AppSidebarLayout>
  );
}

