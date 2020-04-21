<!DOCTYPE html>
  <html>
    <head>
        <title>Demo</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="description" content="Demo Project">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <style type="text/css"> td{padding: 10px}</style>
        <script>
          function getData() {
            var email = document.getElementById('email').value;
            email = encodeURI(email);
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'https://contacts.tobiasweissert.ch/contacts.php?string='+email);
            xhr.send();
            xhr.onload = function() {
              var response = JSON.parse(xhr.response)
              var html = `<tr><td>Vorname:</td><td>${response.firstname.value}</td><td>${response.firstname.gender}</td><td>${response.firstname.origin}</td></tr>
              <tr><td>Nachname:</td><td colspan="2">${response.lastname.value}</td><td>${response.lastname.origin}</td></tr>
              <tr><td>Firma:</td><td colspan="3">${response.company.name}</td></tr>
              <tr><td>Adresse:</td><td colspan="3">${response.company.adress}</td></tr>`
              document.getElementById('result').innerHTML = html;
            };
          }
        </script>
    </head>
    <body>
      <div style="width: 400px; margin-left: auto; margin-right: auto; margin-top: 100px;">
        <p>Type in an E-Mail in the format {name.name@company.domain}</p>
        <input id="email" type="text">
        <button onclick="getData()">Abfragen</button>
        <table id="result" border="1"></table>
      </div>
    </body>
  </html>