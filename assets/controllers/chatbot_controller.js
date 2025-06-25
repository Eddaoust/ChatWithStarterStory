import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["loadingIndicator"]
    static values = {
        isLoading: Boolean,
        component: Object
    }

    connect() {
        this.isLoadingValueChanged()
    }

    isLoadingValueChanged() {
        if (this.isLoadingValue && this.hasLoadingIndicatorTarget) {
            setTimeout(() => {
                this.triggerProcessResponse()
            }, 100)
        }
    }

    triggerProcessResponse() {
        const liveComponent = this.element.closest('[data-live-name-value]')
        if (liveComponent && liveComponent.__component) {
            liveComponent.__component.action('processResponse')
        }
    }
}
