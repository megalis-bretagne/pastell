#!/bin/bash

#Script présent pour des raisons de compatibilité
curl -s https://validca.libriciel.fr/retrieve-validca.sh | bash -s /etc/apache2/ssl
