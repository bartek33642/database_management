<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" />
    <title>DB App Registration!</title>
  </head>
  <body>
    <main class="container">
    <nav>
  <ul>
    <li><strong>DB Reserv</strong></li>
  </ul>
  <ul>
    <li><a href="/zetes2">Home</a></li>
    <li><a href="#">About</a></li>
        <li><a href="sign-out.php">Sign out</a></li>
  </ul>
</nav>
<!-- Preview -->
      <section id="preview">
        <h2>Create database</h2>

        <form>
          <div class="grid">
            <input type="text" name="text" placeholder="Database name" aria-label="Email"  />

            <input type="text" name="text" placeholder="User name" aria-label="Email"  />
            <input type="password" name="password" placeholder="Password" aria-label="Password" />
           
  </div>


        </form>
      </section>
      <!-- ./ Preview -->
<button type="submit">Create database</button>

    </main>
  </body>
</html>