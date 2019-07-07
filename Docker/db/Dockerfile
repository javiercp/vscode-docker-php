FROM mysql

ENV MYSQL_ROOT_USER root
ENV MYSQL_ROOT_PASSWORD secret
ENV MYSQL_DATABASE php_sample
ENV MYSQL_USER db_user
ENV MYSQL_PASSWORD supersecret

COPY ./DBExport.sql /docker-entrypoint-initdb.d/

EXPOSE 3306