import React from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

export default function ItemSearchSelectDemo() {
    return (
        <Card className="w-full max-w-2xl mx-auto">
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    🔍 Selector de Items Mejorado
                </CardTitle>
                <CardDescription>
                    Nuevo componente de búsqueda para seleccionar items de forma más eficiente
                </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 className="font-semibold mb-2">✨ Características</h4>
                        <ul className="space-y-1 text-sm">
                            <li>• Búsqueda por nombre, código o descripción</li>
                            <li>• Navegación con teclado (↑↓ Enter Escape)</li>
                            <li>• Autocompletado inteligente</li>
                            <li>• Límite de 50 resultados para rendimiento</li>
                            <li>• Botón de limpiar selección</li>
                            <li>• Indicador de más resultados disponibles</li>
                        </ul>
                    </div>
                    <div>
                        <h4 className="font-semibold mb-2">⌨️ Atajos de Teclado</h4>
                        <div className="space-y-1 text-sm">
                            <div className="flex justify-between">
                                <span>Abrir/Navegar:</span>
                                <Badge variant="outline">↑ ↓</Badge>
                            </div>
                            <div className="flex justify-between">
                                <span>Seleccionar:</span>
                                <Badge variant="outline">Enter</Badge>
                            </div>
                            <div className="flex justify-between">
                                <span>Cerrar:</span>
                                <Badge variant="outline">Escape</Badge>
                            </div>
                            <div className="flex justify-between">
                                <span>Limpiar:</span>
                                <Badge variant="outline">X</Badge>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 className="font-semibold text-blue-900 mb-2">💡 Consejos de Uso</h4>
                    <ul className="text-sm text-blue-800 space-y-1">
                        <li>• Escribe parte del nombre o código del item</li>
                        <li>• Usa las flechas del teclado para navegar</li>
                        <li>• Presiona Enter para seleccionar</li>
                        <li>• Click en X para limpiar la selección</li>
                    </ul>
                </div>

                <div className="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h4 className="font-semibold text-green-900 mb-2">🚀 Mejoras de Rendimiento</h4>
                    <ul className="text-sm text-green-800 space-y-1">
                        <li>• Solo muestra 50 resultados máximo</li>
                        <li>• Búsqueda optimizada en tiempo real</li>
                        <li>• Cierre automático al hacer click fuera</li>
                        <li>• Carga rápida incluso con miles de items</li>
                    </ul>
                </div>
            </CardContent>
        </Card>
    );
}
