#!/bin/bash

# Inicia o primeiro servidor em segundo plano
php -S 0.0.0.0:8080 &

# Inicia o segundo servidor apontando para a pasta "app"
cd api && php -S 0.0.0.0:8001 &

# Aguarda indefinidamente (impede o container de encerrar)
wait -n

