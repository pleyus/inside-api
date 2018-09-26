# inside-api
Lado del servidor de Inside (api)
## Uso
Para acceder se utilizan peticiones Post con los parametros de acción.
Tambien requiere que se envien los pametros Get using y make para entrar a los diferentes modulos.

**Using:** es el nombre de la carpeta del modulo

**Make:** la acción a realizar (los archivos en PHP)

### Ejemplo:
Para obtener la lista de usuarios (requiere permiso de administrador) se hace la petición:

*/inside-api*/?using=**users**&make=**list**

Todas las peticiones devuelven un json con dos elementos: status y data
```
{ status: 0, data: [] }
```

#### status
Valor numerico que representa el estado de la petición
* 0 = Error
* 1 = Success
* 2 = Warning

#### data
Valor mixto dependiendo de lo que se solicite.
