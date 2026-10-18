# mod_aiqbank — Banco de preguntas con IA para Moodle 4.5

Actividad de Moodle que aparece en **"Añadir una actividad o un recurso"**. El docente la agrega a su curso, la abre, indica temas, nivel y cantidad, y el plugin genera las preguntas con IA y las guarda en el **banco de preguntas del curso**. Después, en cualquier Cuestionario: *Añadir → del banco de preguntas* y se elige la categoría.

## Instalación

1. Copiar la carpeta `aiqbank` a `/var/www/html/mod/aiqbank` (la carpeta debe llamarse exactamente `aiqbank`).
   O bien: *Administración del sitio → Extensiones → Instalar plugins* y subir `mod_aiqbank.zip`.
2. Entrar como administrador para que Moodle actualice la base de datos.
3. Purgar cachés: `php admin/cli/purge_caches.php`

## Configuración del proveedor de IA (administrador)

*Administración del sitio → Extensiones → Módulos de actividad → Banco de preguntas IA*

| Campo | Descripción |
|---|---|
| Proveedor | Google Gemini, OpenAI (ChatGPT), xAI Grok u Otro compatible con OpenAI |
| Clave API | Clave del proveedor (solo la ve el administrador) |
| Modelo | Vacío = modelo predeterminado. Revise los nombres vigentes en su proveedor |
| URL base | Vacío = la del proveedor. Obligatoria en "Otro" (DeepSeek, Ollama, etc.) |
| Temperatura / Tiempo de espera / Preguntas por solicitud / Modo JSON | Ajustes avanzados |

Use el botón **Probar la conexión** después de guardar.

## Uso (docente)

0. La actividad se crea **oculta por defecto** (los estudiantes no la ven).
1. Curso → Activar edición → *Añadir una actividad o un recurso* → **Banco de preguntas IA**.
2. Abrir la actividad e indicar:
   - **Temas** (uno por línea o separados por comas).
   - **Preguntas sin alternativas** (opcional): la IA crea las alternativas y marca la correcta.
   - **Nivel**: Inicial, Primaria, Secundaria, Universitario u otra edad. Cada nivel define la edad y la complejidad cognitiva de las preguntas (de simple reconocimiento en Inicial a análisis y evaluación en Universitario).
   - **Cantidad**: 5, 10, 20, 30, 40 o 50.
   - **Tipo**: opción múltiple, verdadero/falso o mixto.
   - **Categoría** del banco de preguntas (existente o nueva).
   - Opcional: guardar como borrador para revisarlas antes de usarlas.
3. Las preguntas quedan en el banco del curso y se añaden a un Cuestionario desde allí.

## Notas

- Las solicitudes grandes se dividen en lotes (por defecto 10 preguntas por llamada).
- Si el servidor de IA está en una red privada (por ejemplo Ollama local), Moodle lo bloquea por defecto: permita el host en *Seguridad HTTP*.
- Esta versión no incluye copias de seguridad/restauración de la actividad (las preguntas generadas sí se respaldan con el banco de preguntas del curso).
- El contenido que escribe el docente se envía al proveedor de IA configurado.
