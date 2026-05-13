try {
    $options = New-Object OpenQA.Selenium.Chrome.ChromeOptions
    $options.AddArgument('--headless')
    $driver = New-Object OpenQA.Selenium.Chrome.ChromeDriver($options)
    $driver.Navigate().GoToUrl('http://localhost/Pilot-run/HR/Employee_payroll/test_full_salary.html')
    $button = $driver.FindElementByXPath("//button[contains(text(), 'Run Complete Test')]")
    $button.Click()
    Start-Sleep -Seconds 10
    $logs = $driver.Manage().Logs.GetLog('browser')
    if ($logs.Count -eq 0) {
        Write-Host "No logs found"
    } else {
        foreach ($log in $logs) {
            Write-Host $log.Message
        }
    }
} catch {
    Write-Error $_.Exception.Message
} finally {
    if ($driver) { $driver.Quit() }
}
