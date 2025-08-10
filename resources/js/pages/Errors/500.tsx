import { Head, Link } from '@inertiajs/react';
import { AlertTriangle, Home, ArrowLeft, RefreshCw } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface Props {
  message?: string;
}

export default function Error500({ message = 'Error interno del servidor' }: Props) {
  const handleRefresh = () => {
    window.location.reload();
  };

  return (
    <>
      <Head title="500 - Error del servidor" />
      
      <div className="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div className="sm:mx-auto sm:w-full sm:max-w-md">
          <div className="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <div className="text-center">
              <AlertTriangle className="mx-auto h-16 w-16 text-red-500 mb-4" />
              
              <h1 className="text-3xl font-bold text-gray-900 mb-2">500</h1>
              
              <h2 className="text-xl font-semibold text-gray-700 mb-4">
                Error del servidor
              </h2>
              
              <p className="text-gray-600 mb-8">
                {message}
              </p>
              
              <div className="space-y-3">
                <Button 
                  onClick={handleRefresh}
                  variant="outline"
                  className="w-full"
                >
                  <RefreshCw className="h-4 w-4 mr-2" />
                  Intentar de nuevo
                </Button>
                
                <Button 
                  onClick={() => window.history.back()}
                  variant="outline"
                  className="w-full"
                >
                  <ArrowLeft className="h-4 w-4 mr-2" />
                  Volver atrás
                </Button>
                
                <Link href="/">
                  <Button className="w-full">
                    <Home className="h-4 w-4 mr-2" />
                    Ir al inicio
                  </Button>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
