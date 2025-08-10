import React, { useState } from 'react';
import { Head, Link, usePage, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Separator } from '@/components/ui/separator';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { ArrowLeft, Edit, Package, Warehouse, Calendar, Clock, CheckCircle, ArrowRight, Check, AlertTriangle } from 'lucide-react';
import type { BreadcrumbItem } from '@/types';

interface TransferItem {
  id: number;
  item: {
    id: number;
    code: string;
    name: string;
    display_name: string;
    unit?: string;
  };
  amount: number;
  formatted_amount: string;
}

interface Transfer {
  id: number;
  code: string;
  description?: string;
  status: number;
  status_text: string;
  source?: {
    id: number;
    code: string;
    name: string;
    display_name: string;
  };
  destination?: {
    id: number;
    code: string;
    name: string;
    display_name: string;
  };
  items: TransferItem[];
  created_at: string;
  updated_at: string;
}

interface Props {
  transfer: Transfer;
}

export default function Show({ transfer }: Props) {
  const { flash, errors } = usePage().props as any;
  const [showApprovalDialog, setShowApprovalDialog] = useState(false);
  const [isApproving, setIsApproving] = useState(false);

  const handleApproveTransfer = () => {
    setIsApproving(true);

    router.post(`/transfers/${transfer.id}/approve`, {}, {
      onSuccess: () => {
        setShowApprovalDialog(false);
        setIsApproving(false);
      },
      onError: (errors) => {
        setIsApproving(false);
        setShowApprovalDialog(false); // Cerrar el modal cuando hay error
        console.error('Error al aprobar traslado:', errors);
      },
      onFinish: () => {
        setIsApproving(false);
      }
    });
  };

  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Panel de Control', href: '/dashboard' },
    { title: 'Traslados', href: '/transfers' },
    { title: transfer.code, href: `/transfers/${transfer.id}` },
  ];

  return (
    <AuthenticatedLayout breadcrumbs={breadcrumbs}>
      <Head title={`Traslado ${transfer.code}`} />

      <div className="p-6 space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-4">
            <div>
              <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                Traslado {transfer.code}
              </h1>
              <div className="flex items-center space-x-2 mt-1">
                <span className="text-sm font-mono text-gray-600 dark:text-gray-400">
                  {transfer.code}
                </span>
                <Badge variant={transfer.status === 1 ? 'default' : 'secondary'}>
                  {transfer.status_text}
                </Badge>
              </div>
            </div>
          </div>
          <div className="flex space-x-2">
            <Link href="/transfers">
              <Button variant="outline">
                <ArrowLeft className="h-4 w-4 mr-2" />
                Volver
              </Button>
            </Link>
            {transfer.status === 0 && (
              <Button
                onClick={() => setShowApprovalDialog(true)}
                className="bg-green-600 hover:bg-green-700"
              >
                <Check className="h-4 w-4 mr-2" />
                Aprobar Traslado
              </Button>
            )}
            {transfer.status === 0 ? (
              <Link href={`/transfers/${transfer.id}/edit`}>
                <Button variant="outline">
                  <Edit className="h-4 w-4 mr-2" />
                  Editar
                </Button>
              </Link>
            ) : (
              <Button variant="outline" disabled>
                <Edit className="h-4 w-4 mr-2" />
                Editar (Completado)
              </Button>
            )}
          </div>
        </div>

        {/* Flash Messages */}
        {flash?.success && (
          <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md flex items-center gap-2">
            <CheckCircle className="h-4 w-4" />
            {flash.success}
          </div>
        )}

        {flash?.error && (
          <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md flex items-center gap-2">
            <AlertTriangle className="h-4 w-4" />
            {flash.error}
          </div>
        )}

        {/* Validation Errors */}
        {errors?.error && (
          <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md flex items-center gap-2">
            <AlertTriangle className="h-4 w-4" />
            <div>
              {typeof errors.error === 'string' ? (
                <div>{errors.error}</div>
              ) : (
                <div className="space-y-1">
                  {Array.isArray(errors.error) ? (
                    errors.error.map((error: string, index: number) => (
                      <div key={index}>{error}</div>
                    ))
                  ) : (
                    <div>{errors.error}</div>
                  )}
                </div>
              )}
            </div>
          </div>
        )}

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2 space-y-6">
            {/* Basic Information */}
            <Card className="shadow-sm">
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Package className="h-5 w-5" />
                  Información del Traslado
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="text-sm font-medium text-gray-500">Código</label>
                    <p className="text-gray-900 font-mono">{transfer.code}</p>
                  </div>
                  <div>
                    <label className="text-sm font-medium text-gray-500">Estado</label>
                    <div className="mt-1">
                      <Badge variant={transfer.status === 1 ? 'default' : 'secondary'}>
                        {transfer.status_text}
                      </Badge>
                    </div>
                  </div>
                </div>

                {transfer.description && (
                  <div>
                    <label className="text-sm font-medium text-gray-500">Descripción</label>
                    <p className="text-gray-900">{transfer.description}</p>
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Warehouse Information */}
            <Card className="shadow-sm">
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Warehouse className="h-5 w-5" />
                  Almacenes
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                  <div className="text-center">
                    <div className="text-sm font-medium text-gray-500">Origen</div>
                    <div className="text-lg font-semibold text-gray-900">
                      {transfer.source?.name || 'No especificado'}
                    </div>
                    <div className="text-sm text-gray-600 font-mono">
                      {transfer.source?.code || '-'}
                    </div>
                  </div>
                  <div className="flex-shrink-0">
                    <ArrowRight className="h-6 w-6 text-gray-400" />
                  </div>
                  <div className="text-center">
                    <div className="text-sm font-medium text-gray-500">Destino</div>
                    <div className="text-lg font-semibold text-gray-900">
                      {transfer.destination?.name || 'No especificado'}
                    </div>
                    <div className="text-sm text-gray-600 font-mono">
                      {transfer.destination?.code || '-'}
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Items */}
            <Card className="shadow-sm">
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Package className="h-5 w-5" />
                  Items del Traslado ({transfer.items.length})
                </CardTitle>
              </CardHeader>
              <CardContent>
                {transfer.items.length > 0 ? (
                  <div className="rounded-md border overflow-hidden">
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead className="py-3">Código</TableHead>
                          <TableHead className="py-3">Item</TableHead>
                          <TableHead className="py-3 text-right">Cantidad</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {transfer.items.map((transferItem) => (
                          <TableRow key={transferItem.id}>
                            <TableCell className="font-mono text-sm">
                              {transferItem.item.code}
                            </TableCell>
                            <TableCell>
                              <div>
                                <div className="font-medium">{transferItem.item.name}</div>
                                <div className="text-sm text-gray-500">
                                  {transferItem.item.unit && `Unidad: ${transferItem.item.unit}`}
                                </div>
                              </div>
                            </TableCell>
                            <TableCell className="text-right font-medium">
                              {transferItem.formatted_amount}
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </div>
                ) : (
                  <div className="text-center py-8">
                    <Package className="h-12 w-12 text-gray-400 mx-auto mb-3" />
                    <p className="text-gray-500 text-sm">
                      Este traslado no tiene items asociados
                    </p>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          <div className="space-y-6">
            {/* Quick Actions */}
            <Card className="shadow-sm">
              <CardHeader>
                <CardTitle>Acciones Rápidas</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                {transfer.status === 0 && (
                  <>
                    <Button
                      onClick={() => setShowApprovalDialog(true)}
                      className="w-full justify-start bg-green-600 hover:bg-green-700"
                    >
                      <Check className="h-4 w-4 mr-2" />
                      Aprobar traslado
                    </Button>
                    <Separator />
                  </>
                )}

                {transfer.status === 0 ? (
                  <Link href={`/transfers/${transfer.id}/edit`} className="block">
                    <Button variant="outline" className="w-full justify-start">
                      <Edit className="h-4 w-4 mr-2" />
                      Editar traslado
                    </Button>
                  </Link>
                ) : (
                  <Button variant="outline" className="w-full justify-start" disabled>
                    <Edit className="h-4 w-4 mr-2" />
                    Editar traslado (Completado)
                  </Button>
                )}

                <Separator />

                <Link href="/transfers" className="block">
                  <Button variant="ghost" className="w-full justify-start">
                    <ArrowLeft className="h-4 w-4 mr-2" />
                    Volver a la Lista
                  </Button>
                </Link>
              </CardContent>
            </Card>

            {/* Status Information */}
            <Card className="shadow-sm">
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Clock className="h-5 w-5" />
                  Información de Estado
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <label className="text-sm font-medium text-gray-500 flex items-center gap-2">
                    <Calendar className="h-4 w-4" />
                    Fecha de Creación
                  </label>
                  <p className="text-gray-900">
                    {new Date(transfer.created_at).toLocaleDateString('es-ES', {
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit'
                    })}
                  </p>
                </div>

                <div>
                  <label className="text-sm font-medium text-gray-500">Última Actualización</label>
                  <p className="text-gray-900">
                    {new Date(transfer.updated_at).toLocaleDateString('es-ES', {
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit'
                    })}
                  </p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Modal de Confirmación de Aprobación */}
        <Dialog open={showApprovalDialog} onOpenChange={setShowApprovalDialog}>
          <DialogContent className="sm:max-w-md">
            <DialogHeader>
              <DialogTitle className="flex items-center gap-2">
                <Check className="h-5 w-5 text-green-600" />
                Aprobar Traslado
              </DialogTitle>
              <DialogDescription>
                ¿Estás seguro de que deseas aprobar este traslado?
              </DialogDescription>
            </DialogHeader>

            <div className="space-y-4">
              <Alert>
                <AlertTriangle className="h-4 w-4" />
                <AlertDescription>
                  <strong>Esta acción:</strong>
                  <ul className="mt-2 space-y-1 text-sm">
                    <li>• Verificará que hay suficiente stock en el almacén origen</li>
                    <li>• Transferirá las cantidades del almacén origen al destino</li>
                    <li>• Marcará el traslado como completado</li>
                    <li>• <strong>No se puede deshacer</strong></li>
                  </ul>
                </AlertDescription>
              </Alert>

              <div className="bg-gray-50 rounded-lg p-4">
                <div className="text-sm">
                  <div className="font-medium mb-3">Resumen del traslado:</div>
                  <div className="space-y-2">
                    <div>Origen: <span className="font-medium">{transfer.source?.name}</span></div>
                    <div>Destino: <span className="font-medium">{transfer.destination?.name}</span></div>
                    <div>Total de items: <span className="font-medium">{transfer.items.length}</span></div>
                  </div>
                </div>
              </div>

              {/* Lista de items a transferir */}
              {/*<div className="bg-blue-50 rounded-lg p-4 border border-blue-200">*/}
              {/*  <div className="text-sm">*/}
              {/*    <div className="font-medium mb-3 text-blue-800">Items a transferir:</div>*/}
              {/*    <div className="space-y-2 max-h-32 overflow-y-auto">*/}
              {/*      {transfer.items.map((transferItem) => (*/}
              {/*        <div key={transferItem.id} className="flex justify-between items-center py-1">*/}
              {/*          <div className="flex-1">*/}
              {/*            <div className="font-medium text-blue-900">{transferItem.item.name}</div>*/}
              {/*            <div className="text-xs text-blue-600">{transferItem.item.code}</div>*/}
              {/*          </div>*/}
              {/*          <div className="text-right">*/}
              {/*            <div className="font-medium text-blue-900">{transferItem.formatted_amount}</div>*/}
              {/*          </div>*/}
              {/*        </div>*/}
              {/*      ))}*/}
              {/*    </div>*/}
              {/*  </div>*/}
              {/*</div>*/}
            </div>

            <DialogFooter className="gap-2">
              <Button
                variant="outline"
                onClick={() => setShowApprovalDialog(false)}
                disabled={isApproving}
              >
                Cancelar
              </Button>
              <Button
                onClick={handleApproveTransfer}
                disabled={isApproving}
                className="bg-green-600 hover:bg-green-700"
              >
                {isApproving ? (
                  <>
                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    Aprobando...
                  </>
                ) : (
                  <>
                    <Check className="h-4 w-4 mr-2" />
                    Aprobar Traslado
                  </>
                )}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </AuthenticatedLayout>
  );
}

