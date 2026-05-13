const { Builder, By, until } = require('selenium-webdriver');
const chrome = require('selenium-webdriver/chrome');

(async function runTest() {
    let options = new chrome.Options();
    options.addArguments('--headless');
    let driver = await new Builder().forBrowser('chrome').setChromeOptions(options).build();
    try {
        await driver.get('http://localhost/Pilot-run/HR/Employee_payroll/test_full_salary.html');
        let button = await driver.findElement(By.xpath("//button[contains(text(), 'Run Complete Test')]"));
        await button.click();
        await driver.sleep(10000);
        let logs = await driver.manage().logs().get('browser');
        logs.forEach(log => {
            console.log(log.message);
        });
    } catch (err) {
        console.error(err);
    } finally {
        await driver.quit();
    }
})();
