(function(window, document) {
  function createContainer() {
    var c = document.createElement('div');
    c.id = 'pixmora-ui';
    c.style.position = 'fixed';
    c.style.right = '18px';
    c.style.bottom = '18px';
    c.style.zIndex = 99999;
    document.body.appendChild(c);
    return c;
  }
  var container = document.getElementById('pixmora-ui') || createContainer();

  function showToast(msg, type, timeout) {
    type = type || 'info'; timeout = timeout || 4000;
    var t = document.createElement('div');
    t.className = 'pixmora-toast pix-' + type;
    t.innerHTML = '<div>' + msg + '</div>';
    t.style.marginTop = '8px';
    t.style.padding = '12px 16px';
    t.style.borderRadius = '10px';
    t.style.boxShadow = '0 10px 30px rgba(2,6,23,0.12)';
    container.appendChild(t);
    setTimeout(function(){ t.remove(); }, timeout);
  }

  function showLoader(message) {
    var id = 'pixmora-loader';
    if (document.getElementById(id)) return;
    var overlay = document.createElement('div');
    overlay.id = id;
    overlay.style.position = 'fixed';
    overlay.style.left = 0; overlay.style.top = 0;
    overlay.style.right = 0; overlay.style.bottom = 0;
    overlay.style.zIndex = 99998;
    overlay.style.background = 'rgba(12,18,30,0.35)';
    overlay.innerHTML = '<div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);color:#fff"><div style="width:56px;height:56px;border-radius:50%;border:6px solid rgba(255,255,255,0.12);border-top-color:#5ee7df;animation:spin 1s linear infinite"></div></div>';
    document.body.appendChild(overlay);
  }

  function hideLoader() {
    var o = document.getElementById('pixmora-loader');
    if (o) o.remove();
  }

  window.PIXMORA_UI = { toast: showToast, loader: showLoader, hideLoader: hideLoader };
})(window, document);
