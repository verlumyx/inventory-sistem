import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Save, ArrowLeft, Plus, Package, Edit as EditIcon, Trash2, Check, X, CheckCircle } from 'lucide-react';
import ItemSearchSelect from '@/components/ItemSearchSelect';
import { useState } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';

interface Warehouse { id: number; code: string; name: string; display_name: string; }
interface Item { id: number; code: string; name: string; unit: string; display_name: string; }

interface Adjustment { id: number; code: string; description: string|null; warehouse_id: number; type: string; status: number; items: { id:number; item_id:number; amount:number; item: Item }[] }
interface Props { adjustment: Adjustment; warehouses: Warehouse[]; items: Item[]; }

export default function Edit({ adjustment, warehouses, items }: Props) {
  const { flash } = usePage().props as any;
  const safeItems = items ?? [];
  const safeAdjItems = (adjustment?.items ?? []);

  const { data, setData, put, processing, errors } = useForm({
    description: adjustment?.description ?? '',
    warehouse_id: adjustment?.warehouse_id ?? undefined,
    type: adjustment?.type ?? 'positive',
    items: safeAdjItems.map((i) => ({ item_id: i.item_id, amount: i.amount })),
  });

  // Estados para agregar items
  const [selectedItem, setSelectedItem] = useState('');
  const [itemAmount, setItemAmount] = useState('');

  // Estados para editar items
  const [editingIndex, setEditingIndex] = useState<number | null>(null);
  const [editingAmount, setEditingAmount] = useState('');

  const addQuickItem = () => {
    if (!selectedItem || !itemAmount) return;
    const itemId = parseInt(selectedItem);
    const amount = parseFloat(itemAmount);

    if (amount <= 0) return;

    // Si el item ya existe en la lista, suma cantidades
    const existingIdx = data.items.findIndex((row) => row.item_id === itemId);
    if (existingIdx !== -1) {
      const copy = [...data.items];
      const prev = copy[existingIdx].amount;
      const newAmount = Number(prev) + amount;
      copy[existingIdx] = { ...copy[existingIdx], amount: newAmount };
      setData('items', copy);
    } else {
      setData('items', [...data.items, { item_id: itemId, amount }]);
    }

    clearItemForm();
  };

  const clearItemForm = () => {
    setSelectedItem('');
    setItemAmount('');
  };

  const removeItem = (index: number) => {
    setData('items', data.items.filter((_, i) => i !== index));
  };

  const startEditingAmount = (index: number) => {
    setEditingIndex(index);
    setEditingAmount(String(data.items[index].amount));
  };

  const saveEditingAmount = () => {
    if (editingIndex === null) return;

    const amount = parseFloat(editingAmount);
    if (amount <= 0) return;

    const updatedItems = [...data.items];
    updatedItems[editingIndex].amount = amount;
    setData('items', updatedItems);

    setEditingIndex(null);
    setEditingAmount('');
  };

  const cancelEditingAmount = () => {
    setEditingIndex(null);
    setEditingAmount('');
  };

  const submit = () => { put(`/adjustments/${adjustment.id}`); };

  const breadcrumbs = [
    { title: 'Panel de Control', href: '/dashboard' },
    { title: 'Ajustes', href: '/adjustments' },
    { title: `Editar ${adjustment.code}`, href: `/adjustments/${adjustment.id}/edit`, current: true },
  ];

  return (
    <AuthenticatedLayout breadcrumbs={breadcrumbs}>
      <Head title={`Editar ${adjustment.code}`} />

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
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Editar Ajuste</h1>
            <p className="text-muted-foreground">Actualiza la información del ajuste {adjustment.code}</p>
          </div>
          <div className="flex gap-2">
            <Button asChild variant="outline">
              <Link href="/adjustments">
                <ArrowLeft className="h-4 w-4 mr-2" /> Volver
              </Link>
            </Button>
          </div>
        </div>

        {/* Información General */}
        <Card>
          <CardHeader>
            <CardTitle>Información General</CardTitle>
            <CardDescription>Actualiza el almacén y la información del ajuste</CardDescription>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label>Almacén</Label>
                <Select value={String(data.warehouse_id)} onValueChange={(v) => setData('warehouse_id', Number(v))}>
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    {warehouses.map(w => (<SelectItem key={w.id} value={String(w.id)}>{w.display_name}</SelectItem>))}
                  </SelectContent>
                </Select>
                {errors.warehouse_id && <p className="text-sm text-red-600 mt-1">{errors.warehouse_id}</p>}
              </div>
              <div>
                <Label>Tipo de Ajuste</Label>
                <Select value={data.type} onValueChange={(v) => setData('type', v)}>
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="positive">Positivo</SelectItem>
                    <SelectItem value="negative">Negativo</SelectItem>
                  </SelectContent>
                </Select>
                {errors.type && <p className="text-sm text-red-600 mt-1">{errors.type}</p>}
              </div>
            </div>

            <div>
              <Label>Descripción</Label>
              <Textarea rows={3} value={data.description} onChange={(e) => setData('description', e.target.value)} placeholder="Motivo del ajuste (opcional)" />
            </div>
          </CardContent>
        </Card>

        {/* Agregar Items */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Plus className="h-5 w-5" />
              Agregar Items
            </CardTitle>
            <CardDescription>Agrega items al ajuste de forma rápida y dinámica</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
              <div>
                <Label htmlFor="item_select">Item</Label>
                <ItemSearchSelect
                  items={safeItems}
                  value={selectedItem}
                  onValueChange={setSelectedItem}
                  placeholder="Buscar item por nombre o código..."
                />
              </div>
              <div>
                <Label htmlFor="amount">Cantidad</Label>
                <Input
                  id="amount"
                  type="number"
                  step="0.01"
                  min="0.01"
                  placeholder="0.00"
                  value={itemAmount}
                  onChange={(e) => setItemAmount(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter') {
                      e.preventDefault();
                      if (selectedItem && itemAmount) {
                        addQuickItem();
                      }
                    }
                  }}
                />
              </div>
              <div className="flex items-end gap-2">
                <Button type="button" onClick={addQuickItem} disabled={!selectedItem || !itemAmount} className="flex-1">
                  <Plus className="mr-2 h-4 w-4" />
                  Agregar
                </Button>
                <Button type="button" variant="outline" onClick={clearItemForm} disabled={!selectedItem && !itemAmount}>
                  Limpiar
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Items del Ajuste */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Package className="h-5 w-5" />
              Items del Ajuste ({data.items.length})
            </CardTitle>
          </CardHeader>
          <CardContent>
            {data.items.length === 0 ? (
              <div className="py-8 text-center text-muted-foreground">
                <Package className="mx-auto mb-4 h-12 w-12 text-gray-400" />
                <p>No hay items agregados</p>
                <p className="text-sm text-gray-400">Agrega items usando el formulario de arriba</p>
              </div>
            ) : (
              <div className="rounded-md border">
                <table className="w-full text-sm">
                  <thead>
                    <tr className="bg-muted/50">
                      <th className="text-left p-2">Item</th>
                      <th className="text-right p-2">Cantidad</th>
                      <th className="w-[120px] p-2">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    {data.items.map((row, idx) => {
                      const it = safeItems.find(i => i.id === row.item_id);
                      return (
                        <tr key={idx} className="border-t">
                          <td className="p-2">
                            <div className="font-medium">{it?.name}</div>
                            <div className="text-xs text-gray-500">{it?.code} • {it?.unit}</div>
                          </td>
                          <td className="p-2 text-right">
                            {editingIndex === idx ? (
                              <Input
                                type="number"
                                value={editingAmount}
                                onChange={(e) => setEditingAmount(e.target.value)}
                                className="w-20 text-right"
                                step="0.01"
                                min="0.01"
                                data-editing-index={idx}
                                onKeyDown={(e) => {
                                  if (e.key === 'Enter') saveEditingAmount();
                                  if (e.key === 'Escape') cancelEditingAmount();
                                }}
                                autoFocus
                              />
                            ) : (
                              Number(row.amount).toFixed(2)
                            )}
                          </td>
                          <td className="p-2">
                            <div className="flex justify-center gap-1">
                              {editingIndex === idx ? (
                                <>
                                  <Button type="button" variant="ghost" size="sm" onClick={saveEditingAmount}>
                                    <Check className="h-4 w-4 text-green-500" />
                                  </Button>
                                  <Button type="button" variant="ghost" size="sm" onClick={cancelEditingAmount}>
                                    <X className="h-4 w-4 text-gray-500" />
                                  </Button>
                                </>
                              ) : (
                                <>
                                  <Button type="button" variant="ghost" size="sm" onClick={() => startEditingAmount(idx)}>
                                    <EditIcon className="h-4 w-4 text-blue-500" />
                                  </Button>
                                  <Button type="button" variant="ghost" size="sm" onClick={() => removeItem(idx)}>
                                    <Trash2 className="h-4 w-4 text-red-500" />
                                  </Button>
                                </>
                              )}
                            </div>
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            )}
          </CardContent>
        </Card>

        <div className="flex justify-end">
          <Button onClick={submit} disabled={processing}>
            <Save className="h-4 w-4 mr-2" /> Actualizar
          </Button>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}

