# Temporary maintenance image: no PHP or application code. Revert after patching.
FROM nginx:stable-alpine
COPY maintenance/default.conf /etc/nginx/conf.d/default.conf
COPY maintenance/index.html /usr/share/nginx/html/maintenance.html
