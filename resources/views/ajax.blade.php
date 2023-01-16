<!DOCTYPE html>
<html>

<head>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

    <div id="result"></div>

    <button onclick="getData()">Get Data</button>

    <script>
        function getData() {
            $.ajax({
                type: 'GET',
                url: 'http://202.157.177.12/api/field',
                success: function(response) {
                    console.log(response);
                    document.getElementById("result").innerHTML = JSON.stringify(response);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
    </script>
</body>

</html>
