import React, { useState } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus, Package, Trash2, Edit, Check, X, AlertCircle } from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import ItemSearchSelect from '@/components/ItemSearchSelect';
import type { BreadcrumbItem } from '@/types';

interface Warehouse {
  id: number;
  code: string;
  name: string;
  display_name: string;
  default?: boolean;
}

interface Item {
  id: number;
  code: string;
  name: string;
  display_name: string;
  price?: number;
  unit?: string;
}

interface TransferItem {
  item_id: number;
  amount: number;
}

interface Props {
  warehouses: Warehouse[];
  items: Item[];
}

export default function Create({ warehouses, items }: Props) {
  const { data, setData, post, processing, errors } = useForm({
    description: '',
    warehouse_source_id: '',
    warehouse_destination_id: '',
    items: [] as TransferItem[],
  });

  const [selectedItem, setSelectedItem] = useState('');
  const [itemAmount, setItemAmount] = useState('');
  const [showDuplicateModal, setShowDuplicateModal] = useState(false);
  const [editingIndex, setEditingIndex] = useState<number | null>(null);
  const [editingAmount, setEditingAmount] = useState('');

  const addItem = () => {
    if (!selectedItem || !itemAmount) return;

    const itemId = parseInt(selectedItem);
    const amount = parseFloat(itemAmount);

    // Verificar que el item no esté ya agregado
    if (data.items.some((i: any) => i.item_id === itemId)) {
      setShowDuplicateModal(true);
      return;
    }

    setData('items', [...(data.items as any[]), { item_id: itemId, amount }]);
    setSelectedItem('');
    setItemAmount('');
  };

  const removeItem = (index: number) => {
    setData('items', (data.items as any[]).filter((_, i) => i !== index));
  };

  // Funciones para editar cantidad
  const startEditingAmount = (index: number) => {
    setEditingIndex(index);
    setEditingAmount(data.items[index].amount.toString());
  };

  const cancelEditingAmount = () => {
    setEditingIndex(null);
    setEditingAmount('');
  };

  const saveEditingAmount = () => {
    if (editingIndex === null || !editingAmount) return;

    const newAmount = parseFloat(editingAmount);
    if (newAmount <= 0) return;

    const updatedItems = [...data.items];
    updatedItems[editingIndex] = {
      ...updatedItems[editingIndex],
      amount: newAmount,
    };

    setData('items', updatedItems);
    setEditingIndex(null);
    setEditingAmount('');
  };

  // Funciones helper
  const getItemById = (itemId: number): Item | undefined => {
    return items.find(item => item.id === itemId);
  };

  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Panel de Control', href: '/dashboard' },
    { title: 'Traslados', href: '/transfers' },
    { title: 'Crear', href: '/transfers/create' },
  ];

  return (
    <AuthenticatedLayout breadcrumbs={breadcrumbs}>
      <Head title="Nuevo Traslado" />

      <div className="p-6 space-y-6">
        <div className="flex justify-between items-start">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Nuevo Traslado</h1>
            <p className="text-gray-600 mt-1">
              Crea un nuevo traslado entre almacenes
            </p>
          </div>
        </div>

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

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
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
              <CardDescription>
                Agrega items al traslado de forma rápida y dinámica
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <Label htmlFor="item_select">Item</Label>
                  <ItemSearchSelect
                    items={items}
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
                  />
                </div>
                <div className="flex items-end">
                  <Button
                    type="button"
                    onClick={addItem}
                    disabled={!selectedItem || !itemAmount}
                    className="w-full"
                  >
                    <Plus className="mr-2 h-4 w-4" />
                    Agregar
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Items del Traslado */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Package className="h-5 w-5" />
                Items del Traslado ({(data.items as any[]).length})
              </CardTitle>
            </CardHeader>
            <CardContent>
              {(data.items as any[]).length === 0 ? (
                <div className="text-center py-8">
                  <Package className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                  <p className="text-gray-500">No hay items agregados</p>
                  <p className="text-sm text-gray-400">Agrega items usando el formulario de arriba</p>
                </div>
              ) : (
                <div className="rounded-md border">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Item</TableHead>
                        <TableHead className="text-right">Cantidad</TableHead>
                        <TableHead className="w-[100px]">Acciones</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {(data.items as any[]).map((transferItem: any, index: number) => {
                        const item = getItemById(transferItem.item_id);
                        return (
                          <TableRow key={index}>
                            <TableCell>
                              <div>
                                <div className="font-medium">{item?.name}</div>
                                <div className="text-sm text-gray-500">
                                  {item?.code} • {item?.unit}
                                </div>
                              </div>
                            </TableCell>
                            <TableCell className="text-right">
                              {editingIndex === index ? (
                                <Input
                                  type="number"
                                  value={editingAmount}
                                  onChange={(e) => setEditingAmount(e.target.value)}
                                  className="w-20 text-right"
                                  step="0.01"
                                  min="0.01"
                                  onKeyDown={(e) => {
                                    if (e.key === 'Enter') saveEditingAmount();
                                    if (e.key === 'Escape') cancelEditingAmount();
                                  }}
                                  autoFocus
                                />
                              ) : (
                                Number(transferItem.amount).toFixed(2)
                              )}
                            </TableCell>
                            <TableCell>
                              <div className="flex gap-1">
                                {editingIndex === index ? (
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
                                    <Button type="button" variant="ghost" size="sm" onClick={() => startEditingAmount(index)}>
                                      <Edit className="h-4 w-4 text-blue-500" />
                                    </Button>
                                    <Button type="button" variant="ghost" size="sm" onClick={() => removeItem(index)}>
                                      <Trash2 className="h-4 w-4 text-red-500" />
                                    </Button>
                                  </>
                                )}
                              </div>
                            </TableCell>
                          </TableRow>
                        );
                      })}
                    </TableBody>
                  </Table>
                </div>
              )}
              {errors.items && (
                <p className="text-sm text-red-600 mt-2">{errors.items}</p>
              )}
            </CardContent>
          </Card>

          <div className="flex gap-2 justify-end">
            <Button type="button" variant="secondary" asChild><Link href="/transfers">Cancelar</Link></Button>
            <Button type="submit" disabled={processing}>Guardar</Button>
          </div>
        </form>

        <Dialog open={showDuplicateModal} onOpenChange={setShowDuplicateModal}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Item duplicado</DialogTitle>
              <DialogDescription>
                Este item ya está agregado al traslado. No se pueden agregar items duplicados.
              </DialogDescription>
            </DialogHeader>
            <DialogFooter>
              <Button onClick={() => setShowDuplicateModal(false)}>
                Entendido
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </AuthenticatedLayout>
  );
}

