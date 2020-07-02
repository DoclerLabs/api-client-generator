#!/usr/bin/env sh

if [ -z "$NAMESPACE" ]; then
      NAMESPACE=$(php composerParser.php --namespace)
      echo "Detected namespace: ${NAMESPACE}"
fi
if [ -z "$PACKAGE" ]; then
      PACKAGE=$(php composerParser.php --name)
      echo "Detected package name: ${PACKAGE}"
fi

/generator/bin/api-client-generator generate \
  --openapi-file=${OPENAPI} \
  --namespace="${NAMESPACE}" \
  --output-directory=${OUTPUT_DIR} \
  --code-style-config=${CODE_STYLE} \
  --package-name=${PACKAGE}
