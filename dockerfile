FROM mariadb:latest

LABEL maintainer="Florian ISAK <florian.isak@icloud.com>"

EXPOSE 3306

CMD ["mysqld"]