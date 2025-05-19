# Weather App

A Symfony-based weather application built with Domain-Driven Design (DDD) principles.

Demo: <https://weather.symfonystudio.com/>

## Installation

1. **Clone the repository**

   ```sh
   git clone https://github.com/galcvua/weather.git
   cd weather
   ```

2. **Install dependencies**

   ```sh
   composer install
   ```

3. **Configure environment variables**
   - Create `.env.local` and set your API key:

     ```env
     WHETHER_API_KEY=your_weather_api_key
     ```

    You can obtain an API key at <https://www.weatherapi.com/>.

4. **Set up writable directories**

   ```sh
   mkdir -p var/cache var/log
   chmod -R 777 var
   ```

   Note: These commands are for Unix-like systems. On Windows, create the directories manually.
5. **Configure parameters**
   - Edit `config/packages/weather.yaml`

     ```yaml
     parameters:
         weather.cities:
             - London
             - Kyiv
             - Warsaw
         weather.ttl: 600
     ```

6. **Compile assets**

   ```sh
   php bin/console asset-map:compile
   ```

7. **Run the application**

   ```sh
   symfony serve
   ```

  Alternatively, you can use your preferred web server.

## Running Tests

- **Unit and functional tests:**

  ```sh
  php bin/phpunit
  ```

## Implementation Notes

- **Domain-Driven Design (DDD):**
  - The project is structured according to DDD principles.
  - The `Domain` layer contains business logic and value objects.
  - The domain code has **no dependencies on Symfony** or any infrastructure/framework code.
  - Infrastructure (API providers, cache, etc.) and presentation (controllers, templates) are separated from the domain.

- **Weather Providers:**
  - Weather data is fetched via a provider interface, allowing easy swapping or mocking for tests.
  - Caching is implemented as a decorator, keeping the domain logic clean.

- **Configuration:**
  - Cities and cache TTL are configured via Symfony parameters.
  - For tests, parameters can be overridden in `config/packages/test/weather.yaml`.

- **Domain Events for Logging:**
  - The application uses domain events to log important actions, such as successful weather data fetches.
  - This approach decouples logging from business logic, making it easy to extend or change logging behavior without modifying domain code.

- **Custom Exception Handling:**
  - All errors related to weather data fetching are handled via a custom exception.
  - This ensures that domain and application layers are not tightly coupled to framework exceptions.
  - The use of custom exceptions makes error handling explicit and testable, and allows for consistent error reporting throughout the application.

- **Testing:**
  - The project uses PHPUnit for both unit and functional tests.
  - Test doubles (stubs/mocks) are used for external dependencies.
  - In-memory cache is used in the test environment for speed and isolation.

- **Frontend:**
  - Uses Turbo Frames for dynamic weather updates.
  - Simple, system-font-based CSS for a clean UI.

**This project demonstrates a clean separation of concerns and is easily extensible for new weather providers or UI features.**
