<html>
  <body>
    <h2>{{ $data['user'] }} has sent you a reminder!</h2>
    <p>Just a quick reminder from {{ $data['user'] }} that you owe them $<strong>{{ $data['amount'] }}</strong> from <strong>{{ $data['date'] }}</strong></p>
    <p>Thank you!</p>
  </body>
</html>