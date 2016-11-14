  <section id="about">
      <div class="container-fluid">
          <div class="row">
            <br><br>  
            <div class="col-lg-12 wow fadeIn">
                  <div class="row">
                    <h1 class="text-center">Login</h1>
                  </div>

                 <br>
                 <br>

                  <div class="row">
                    <div class="col-md-4 col-md-offset-4">
                        <div class="well">
                            <form method="post" action="/home/login" accept-charset="utf-8">
                            <div class="form-group">
                                <label for="username">Gebruikersnaam</label>
                                <input type="text" class="form-control" id="username" name="name" placeholder="Gebruikersnaam" minlength=4 maxlength=20 required>
                            </div>
                            <div class="form-group">
                                <label for="password">Paswoord</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Paswoord" minlength=12 maxlength=100 required>
                            </div>

                            <br>
    
                            <? if ($this->captcha): ?>
                            <div class="col-md-4">
                                <input class="btn btn-lg btn-block btn-info" type="submit" value="Login">
                            </div>
                            <div class="col-md-8">
                                <div class="pull-right g-recaptcha" data-sitekey="6LcJqgkUAAAAAApuufS4zbcvuk5FBJ5xRS3xGZIu"></div>
                            </div>
                            <div class="row"></div>
                            <? else: ?>
                            <input class="btn btn-lg btn-block btn-info" type="submit" value="Login">
                            <? endif; ?>
                            </form> 
                        </div>
                    </div>
                </div>                 
              </div>
          </div>
          <div class="row text-center content-row">
          </div>
      </div>
  </section>

