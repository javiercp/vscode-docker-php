# VSCODE PHP Sample

Ejemplo de proyecto PHP con Docker y VSCode.

## Entorno Docker

Dentro de la carpeta Docker hay un fichero "docker-compose.yml" para utilizar con docker-compose y que levanta todo el entorno de desarrollo. 

Se puede ejecutar manualmente por ejemplo levantando el entorno en background:

```console
docker-compose up -d
```

Este fichero levanta los contenedores necesarios y crea los enlaces entre ellos (para que el contenedor de PHP vea la base de datos y para que el apache vea el PHP-FMP).

El entorno está compuesto de 3 contenedores:

- Base de datos
- PHP-FPM
- Apache

Tanto en el contenedor de PHP-FPM como en el apache se añade la carpeta con el código como un volumen:

```docker
    volumes:
      - ../:/var/www/html/public
``` 

En el contenedor de base de datos especificamos que no utilice los nuevos mecanismos de autenticación (que todavia no funcionan en PHP):

```docker
    command: --default-authentication-plugin=mysql_native_password
```

### Base de datos

Este contenedor tiene una base de datos MySql. En el fichero Dockerfile se definen las variables de entorno con las que se configura la base de datos:

```docker
ENV MYSQL_ROOT_USER root
ENV MYSQL_ROOT_PASSWORD secret
ENV MYSQL_DATABASE php_sample
ENV MYSQL_USER db_user
ENV MYSQL_PASSWORD supersecret
```

En el arranque del contenedor e crea automáticamente una base de datos con el nombre definido en MYSQL_DATABASE y se crea un usuario administrador de esa base de datos con el nombre definido en MYSQL_USER y la contraseña definida en MYSQL_PASSWORD.

También se copia en la carpeta "docker-entrypoint-initdb.d" un fichero con las sentencias SQL que se deben ejecutar en esa base de datos. En este caso la creación de unas tablas de ejemplo.

### PHP-FPM

Este contenedor tiene el entorno de ejecución de PHP. Está basado en el que  genera el Visual Studio como entorno de desarrollo remoto en PHP. He cambiado la imagen para utilizar FPM (lo que permitirá tener un frontal con Apache y que este contenedor ejecute el codigo de los scripts PHP).

Como el servidor de debug remoto (XDebug) y el FPM utilizan el mismo puerto (el 9000) se cambia el puerto en el que se arranca el servidor de debug al 9009:

```docker
&& echo "xdebug.remote_port=9009" >> /usr/local/etc/php/conf.d/xdebug.ini \
```

Luego se actualizará la configuración del VSCode para que utilice este puerto.

También se instala un módulo adicional para utilizar la base de datos:

```docker
RUN docker-php-ext-install mysqli
```

### Apache

Este contenedor tiene el servidor Apache que va a hacer de frontal de la aplicación. A la instalación de Apache se le copian dos ficheros, uno para ejecutar el servidor en primer plano (apache2-foreground) y otro que configura la ejecución de los scripts PHP (000-default.conf). En este último esta es la linea clave:

```config
ProxyPassMatch ^/(.*\.php(/.*)?)$ fcgi://phpfpm:9000/var/www/html/public/$1
```

En ella se indica que los scripts deben ser pasados para su ejecución al servidor phpfpm. Cuando se crean los contenedores desde el docker-compose automáticamente se añaden a los ficheros host las entradas para que se vean entre si. "phpfpm" es el nombre con el que está definido el servicio de PHP en el fichero docker-compose.yml

## VS Code

Dentro de la carpeta .devcontainer hay un fichero devcontainer.json donde configuramos el entorno. En el se modifican (o añaden) tres propiedades:

```json
	"dockerComposeFile": "../Docker/docker-compose.yml",
	"service": "phpfpm",
	"workspaceFolder": "/var/www/html/public",
```

- dockerComposeFile, le indica al VSCode que debe utilizar el fichero docker-compose para levantar el entorno.
- service, indica a cual de los servicios levantados en el docker-compose se debe conectar para trabajar.
- workspaceFolder, indica en que carpeta del servicio anterior están los ficheros con los que va a trabajar.

Si abrimos la carpeta con el VSCode Insiders, con las extensiones de desarrollo remoto instaladas, automáticamente detecta que existe la carpeta ".devcontainer" y pregunta si deseamos abrir el proyecto en el contendor remoto. En caso afirmativo crea el entorno con el fichero docker-compose: construye las imágenes si es necesario, arranca los contenedores y se conecta al servicio indicado.

### Debug remoto

Como hemos cambiado el puerto donde se ejecuta el servidor de debug es necesario modificar la configuración del VSCode para reflejar este cambio. Para ello en la carpeta .vscode modificamos el fichero launch.json para indicar el nuevo puerto:

```json
    "configurations": [
        {
            "name": "Listen for XDebug",
            "type": "php",
            "request": "launch",
            "port": 9009
        },
        {
            "name": "Launch currently open script",
            "type": "php",
            "request": "launch",
            "program": "${file}",
            "cwd": "${fileDirname}",
            "port": 9009
        }
    ]
```

## Enlaces interesantes

Estos enlaces han servido de inspiración y ayuda para configurar este proyecto.

- Enlace genial que enseña todo el proceso de configuración del entorno en docker. A partir de este enlace es donde he obtenido los ficheros que se utilizan en la configuración del contenedor PHP-FPM y Apache:
<https://blog.irontec.com/desarrollando-con-docker/>

- Ejemplo super completo de configuración de un entorno docker:
<http://www.inanzzz.com/index.php/post/su76/creating-apache-mysql-and-php-fpm-containers-for-a-web-application-with-docker-compos>

- Página oficial con las imágenes docker de PHP:
<https://hub.docker.com/_/php>

- Información sobre el desarrollo remoto con contenedores en VSCode:
<https://code.visualstudio.com/docs/remote/remote-overview>

