import { Controller } from '@hotwired/stimulus';

/*
 * This is a Stimulus controller!
 *
 * Any element with a data-controller="weather" attribute will cause
 * this controller to be executed. The name "weather" comes from the filename:
 * weather_controller.js -> "weather"
 *
 */
export default class extends Controller {
    connect() {
        const frame = this.element.querySelector('turbo-frame#weather');

        if (!frame) {
            console.warn('No turbo-frame found with id "weather"');
            return;
        }

        const data = this.element.dataset;

        const errorListener = event => {
            console.warn('Error loading weather frame', event);

            event.preventDefault();
            frame.innerHTML = `<div class="error">${ data.errorMessage }</div>`;
        }

        frame.addEventListener('turbo:fetch-request-error', errorListener);
        frame.addEventListener('turbo:frame-missing', errorListener);

        const updateInterval = data.interval;

        const reloadFrame = () => {
            frame.reload();
            setTimeout(reloadFrame, updateInterval);
        };

        if (updateInterval ) {
            setTimeout(reloadFrame, updateInterval);
        }
    }
}
