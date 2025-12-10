# alternative HTTP client

Change the HTTP client for API requests.

## Dependencies

Your plugin requires a PSR compatible HTTP client. e.g.:

- `nimbly/shuttle` +
- `nyholm/psr7`

## Extension

The magic happens as extension of Logger configuration, defined in the services.yaml and implemented in ConfigurationExtension class. A metadata registered class extension is not neccessary.