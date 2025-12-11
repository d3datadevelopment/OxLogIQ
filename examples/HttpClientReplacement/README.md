# alternative HTTP client

Replaces the HTTP client for API requests. This allows you to use your preferred PSR 7 / PSR 18 client or reconfigure the existing one to meet your requirements.

## Dependencies

Your plugin requires a PSR compatible HTTP client. e.g.:

- `nimbly/shuttle` +
- `nyholm/psr7`

## Extension

The magic happens as extension of Logger configuration, defined in the services.yaml and implemented in ConfigurationExtension class. A metadata registered class extension is not neccessary.