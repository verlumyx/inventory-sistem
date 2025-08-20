import { Head, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AlertCircle, Save, Building, CheckCircle, Info } from 'lucide-react';
import { Alert, AlertDescription } from '@/components/ui/alert';
import HeadingSmall from '@/components/heading-small';
import { type BreadcrumbItem } from '@/types';

interface Company {
    id: number;
    name_company: string;
    dni: string;
    address: string;
    phone: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    company: Company;
}

interface FormData {
    name_company: string;
    dni: string;
    address: string;
    phone: string;
}

export default function CompanySettings({ company }: Props) {
    const { flash, errors: pageErrors } = usePage().props as any;
    const { data, setData, put, processing, errors } = useForm<FormData>({
        name_company: company.name_company || '',
        dni: company.dni || '',
        address: company.address || '',
        phone: company.phone || '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put('/settings/company');
    };

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Configuración de Empresa',
            href: '/settings/company',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Configuración - Empresa" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Configuración de Empresa"
                        description="Configura los datos de tu empresa que aparecerán en las facturas y reportes."
                    />

                    {/* Flash Messages */}
                    {flash?.success && (
                        <Alert className="border-green-200 bg-green-50 text-green-800">
                            <CheckCircle className="h-4 w-4" />
                            <AlertDescription>{flash.success}</AlertDescription>
                        </Alert>
                    )}

                    {flash?.error && (
                        <Alert variant="destructive">
                            <AlertCircle className="h-4 w-4" />
                            <AlertDescription>{flash.error}</AlertDescription>
                        </Alert>
                    )}

                    {pageErrors?.error && (
                        <Alert variant="destructive">
                            <AlertCircle className="h-4 w-4" />
                            <AlertDescription>{pageErrors.error}</AlertDescription>
                        </Alert>
                    )}

                    {/* Company Configuration Form */}
                    <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Building className="h-5 w-5" />
                            Datos de la Empresa
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {/* Nombre de la Empresa */}
                                <div className="space-y-2">
                                    <Label htmlFor="name_company">
                                        Nombre de la Empresa <span className="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="name_company"
                                        type="text"
                                        value={data.name_company}
                                        onChange={(e) => setData('name_company', e.target.value)}
                                        placeholder="Ej: Mi Empresa C.A."
                                        className={errors.name_company ? 'border-red-500' : ''}
                                    />
                                    {errors.name_company && (
                                        <p className="text-sm text-red-600">{errors.name_company}</p>
                                    )}
                                </div>

                                {/* DNI/RIF */}
                                <div className="space-y-2">
                                    <Label htmlFor="dni">
                                        RIF/DNI <span className="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="dni"
                                        type="text"
                                        value={data.dni}
                                        onChange={(e) => setData('dni', e.target.value)}
                                        placeholder="Ej: J-12345678-9"
                                        className={errors.dni ? 'border-red-500' : ''}
                                    />
                                    {errors.dni && (
                                        <p className="text-sm text-red-600">{errors.dni}</p>
                                    )}
                                </div>
                            </div>

                            {/* Dirección */}
                            <div className="space-y-2">
                                <Label htmlFor="address">
                                    Dirección <span className="text-red-500">*</span>
                                </Label>
                                <Textarea
                                    id="address"
                                    value={data.address}
                                    onChange={(e) => setData('address', e.target.value)}
                                    placeholder="Dirección completa de la empresa"
                                    rows={3}
                                    className={errors.address ? 'border-red-500' : ''}
                                />
                                {errors.address && (
                                    <p className="text-sm text-red-600">{errors.address}</p>
                                )}
                            </div>

                            {/* Teléfono */}
                            <div className="space-y-2">
                                <Label htmlFor="phone">
                                    Teléfono <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="phone"
                                    type="text"
                                    value={data.phone}
                                    onChange={(e) => setData('phone', e.target.value)}
                                    placeholder="Ej: +58 412-123-4567"
                                    className={errors.phone ? 'border-red-500' : ''}
                                />
                                {errors.phone && (
                                    <p className="text-sm text-red-600">{errors.phone}</p>
                                )}
                            </div>

                            {/* Submit Button */}
                            <div className="flex justify-end">
                                <Button type="submit" disabled={processing}>
                                    <Save className="mr-2 h-4 w-4" />
                                    {processing ? 'Guardando...' : 'Guardar Configuración'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                    {/* Information Alert */}
                    <Alert>
                        <Info className="h-4 w-4" />
                        <AlertDescription>
                            Esta información se utilizará en las facturas impresas y otros documentos oficiales de la empresa.
                            Asegúrate de que todos los datos sean correctos y estén actualizados.
                        </AlertDescription>
                    </Alert>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
