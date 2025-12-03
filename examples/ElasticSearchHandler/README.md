# ElasticSearch / ELK stack

Add a handler for Elastic Search. All other handlers and processors are kept in place.

## Dependencies

Your plugin requires an ElasticSearch client library. e.g.:

- `ruflin/elastica`

## Extension

The magic happens as extension of Logger Factory, defined in the services.yaml and implemented in LoggerFactoryExtension class. A metadata registered class extension is not neccessary.