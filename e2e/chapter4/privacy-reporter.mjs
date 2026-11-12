/**
 * Remove captured DOM and dynamic locator values from the HTML report.
 * The checks still run unchanged; aggregate and case-ID attachments remain.
 */
export default class PrivacyReporter {
    printsToStdio() {
        return false;
    }

    onStepBegin(_test, _result, step) {
        step.title = `[${step.category}]`;
        step.subtitle = undefined;
    }

    onStepEnd(_test, _result, step) {
        step.title = `[${step.category}]`;
        step.subtitle = undefined;
        if (step.error) this.cleanError(step.error);
    }

    onTestEnd(_test, result) {
        const excluded = /^(error-context|screenshot|trace|coordinate-samples|marker-samples)/i;
        for (const attachment of result.attachments) {
            if (excluded.test(attachment.name)) {
                attachment.path = undefined;
                attachment.body = Buffer.from('Redacted to protect private incident data.');
                attachment.contentType = 'text/plain';
            }
        }
        for (const error of result.errors) this.cleanError(error);
        if (result.error) this.cleanError(result.error);
    }

    cleanError(error) {
        if (error.message) error.message = error.message.split('\n')[0];
        if (error.stack) error.stack = '';
        if (error.snippet) error.snippet = '';
        if (error.value) error.value = '';
    }
}
