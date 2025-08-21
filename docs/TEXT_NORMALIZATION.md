# 🔤 Normalización de Texto para Impresión Térmica

Esta guía explica cómo usar la funcionalidad de normalización de texto para eliminar automáticamente acentos y caracteres especiales que pueden causar problemas en impresoras térmicas.

## 🎯 Problema que Resuelve

Las impresoras térmicas de 58mm suelen tener problemas con:
- **Acentos**: á, é, í, ó, ú, ñ
- **Signos especiales**: ¡, ¿, –, —
- **Comillas especiales**: ", ", ', '
- **Caracteres Unicode**: que no están en ASCII básico

Estos caracteres pueden aparecer como:
- Símbolos extraños: `�`, `?`, `□`
- Espacios en blanco
- Caracteres incorrectos

## ✅ Solución Automática

El `PrintService` ahora incluye una función `normalizeText()` que automáticamente:

1. **Convierte acentos** a letras normales
2. **Elimina signos problemáticos** como ¡ y ¿
3. **Normaliza guiones** especiales a guiones normales
4. **Mantiene la legibilidad** del texto

## 🚀 Uso Automático

**Todas las funciones de formato ya aplican normalización automáticamente:**

```php
// Estos métodos YA normalizan el texto automáticamente:
$printService->centerText("Panadería El Buen Sabor");  // → "Panaderia El Buen Sabor"
$printService->rightAlign("Almacén Principal");        // → "Almacen Principal"  
$printService->wrapText("Descripción del producto");   // → "Descripcion del producto"
```

## 🔧 Uso Manual

**Para normalizar texto fuera del contexto de impresión:**

```php
use App\Services\PrintService;

$printService = new PrintService();

// Ejemplos de normalización
$normalized = $printService->normalizeText("¡Panadería El Buen Sabor!");
// Resultado: "Panaderia El Buen Sabor!"

$normalized = $printService->normalizeText("Almacén Principal");
// Resultado: "Almacen Principal"

$normalized = $printService->normalizeText("Descripción del producto");
// Resultado: "Descripcion del producto"
```

## 📝 Ejemplos de Conversión

| Texto Original | Texto Normalizado |
|---|---|
| `Panadería "El Buen Sabor"` | `Panaderia "El Buen Sabor"` |
| `¡Gracias por su compra!` | `Gracias por su compra!` |
| `Almacén Principal` | `Almacen Principal` |
| `Descripción del producto` | `Descripcion del producto` |
| `Niño pequeño` | `Nino pequeno` |
| `Configuración avanzada` | `Configuracion avanzada` |
| `Texto–separado` | `Texto-separado` |

## 🎨 Casos de Uso

### 1. Nombres de Empresa
```php
// Antes: "Panadería San José"
// Después: "Panaderia San Jose"
$company->name = $printService->normalizeText($company->name);
```

### 2. Nombres de Almacén
```php
// Antes: "Almacén Principal"
// Después: "Almacen Principal"
$warehouse->name = $printService->normalizeText($warehouse->name);
```

### 3. Descripciones de Productos
```php
// Antes: "Descripción del artículo"
// Después: "Descripcion del articulo"
$item->description = $printService->normalizeText($item->description);
```

### 4. Direcciones
```php
// Antes: "Av. Bolívar, Edif. Comercial"
// Después: "Av. Bolivar, Edif. Comercial"
$address = $printService->normalizeText($company->address);
```

## 🔍 Caracteres Soportados

### Vocales con Acentos
- **Minúsculas**: á → a, é → e, í → i, ó → o, ú → u
- **Mayúsculas**: Á → A, É → E, Í → I, Ó → O, Ú → U
- **Otros**: à, ä, â, ā, ã, è, ë, ê, ē, etc.

### Caracteres Especiales
- **Ñ/ñ**: Ñ → N, ñ → n
- **Ç/ç**: Ç → C, ç → c

### Signos de Puntuación
- **Exclamación**: ¡ → (eliminado)
- **Interrogación**: ¿ → (eliminado)
- **Guiones**: – → -, — → -

## ⚡ Rendimiento

- **Rápido**: Usa `strtr()` que es muy eficiente
- **Ligero**: No requiere extensiones adicionales
- **Confiable**: Funciona en cualquier instalación de PHP

## 🧪 Testing

Puedes probar la normalización con:

```bash
php artisan test --filter=it_can_normalize_text_with_accents_and_special_characters
```

## 💡 Consejos

1. **Usa automáticamente**: Las funciones de formato ya normalizan el texto
2. **Para casos especiales**: Usa `normalizeText()` manualmente
3. **Mantén originales**: Normaliza solo para impresión, no en base de datos
4. **Prueba siempre**: Verifica que el texto se vea bien en tu impresora

## 🔧 Personalización

Si necesitas agregar más caracteres, edita el array `$replacements` en:
`app/Services/PrintService.php` → método `normalizeText()`

```php
$replacements = [
    // Agregar más caracteres aquí
    'ü' => 'u',
    'Ü' => 'U',
    // etc...
];
```
