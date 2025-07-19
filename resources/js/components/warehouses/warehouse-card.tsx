import { Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Eye, Edit, Calendar, Hash } from 'lucide-react';

interface Warehouse {
    id: number;
    code: string;
    name: string;
    description: string | null;
    status: boolean;
    status_text: string;
    created_at: string;
    updated_at: string;
}

interface WarehouseCardProps {
    warehouse: Warehouse;
    showActions?: boolean;
}

export default function WarehouseCard({ warehouse, showActions = true }: WarehouseCardProps) {
    return (
        <Card className="hover:shadow-md transition-shadow">
            <CardHeader className="pb-3">
                <div className="flex items-start justify-between">
                    <div className="space-y-1">
                        <CardTitle className="text-lg">
                            <Link 
                                href={`/warehouses/${warehouse.id}`}
                                className="hover:text-primary transition-colors"
                            >
                                {warehouse.name}
                            </Link>
                        </CardTitle>
                        <div className="flex items-center space-x-2">
                            <div className="flex items-center text-sm text-muted-foreground">
                                <Hash className="h-3 w-3 mr-1" />
                                <span className="font-mono">{warehouse.code}</span>
                            </div>
                            <Badge variant={warehouse.status ? 'default' : 'secondary'}>
                                {warehouse.status_text}
                            </Badge>
                        </div>
                    </div>
                </div>
            </CardHeader>
            
            <CardContent className="space-y-4">
                {warehouse.description && (
                    <CardDescription className="line-clamp-2">
                        {warehouse.description}
                    </CardDescription>
                )}
                
                <div className="flex items-center text-xs text-muted-foreground">
                    <Calendar className="h-3 w-3 mr-1" />
                    <span>
                        Creado el {new Date(warehouse.created_at).toLocaleDateString('es-ES')}
                    </span>
                </div>

                {showActions && (
                    <div className="flex items-center space-x-2 pt-2">
                        <Link href={`/warehouses/${warehouse.id}`}>
                            <Button variant="outline" size="sm">
                                <Eye className="h-3 w-3 mr-1" />
                                Ver
                            </Button>
                        </Link>
                        <Link href={`/warehouses/${warehouse.id}/edit`}>
                            <Button variant="outline" size="sm">
                                <Edit className="h-3 w-3 mr-1" />
                                Editar
                            </Button>
                        </Link>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
