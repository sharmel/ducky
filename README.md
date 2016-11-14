# ducky
Simple web framework with security features

-- How does it work?

Look at the `Home.php` controller in `application/controllers/`. This handles your requests and then renders the default layout in `application/views/.layouts/`, partials from `application/views/.partials/` and the current page. E.g. `$this->render('home/index');` will render the `index.php` view in `application/views/home/`.
