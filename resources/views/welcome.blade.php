<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head></head>

<body class="">
    <button onclick="exportData()">Export</button>
</body>
<script>
    async function exportData() {
        try {
            const data = await fetch("http://127.0.0.1:8000/api/export")

            const blob = await data.blob()
            const url = window.URL.createObjectURL(blob)
            const a = document.createElement('a')
            a.href = url
            a.download = "export.xlsx"
            document.body.appendChild(a)
            a.click()
            window.URL.revokeObjectURL(url)
        }
        catch (e) {
            console.log("e", e)
        }
    }
</script>

</html>