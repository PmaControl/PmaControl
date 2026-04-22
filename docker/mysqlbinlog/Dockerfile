ARG MYSQL_VERSION=8.0
FROM mysql:${MYSQL_VERSION} AS base
# mysql official image is Oracle Linux / EL9 based with minimal server.
# Install the full client package which includes mysqlbinlog.
RUN microdnf install -y mysql-community-client && microdnf clean all
ENTRYPOINT ["mysqlbinlog"]
