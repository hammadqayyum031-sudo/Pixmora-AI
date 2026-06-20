document.addEventListener('DOMContentLoaded', function() {
  async function postForm(data) {
    const res = await fetch('process.php', { method: 'POST', body: data });
    return res.json();
  }

  const processBtn = document.getElementById('processBtn');
  const imageFileInput = document.getElementById('imageFile');
  const engineSelect = document.getElementById('engineSelect');
  const processingStatus = document.getElementById('processingStatus');
  const previewResult = document.getElementById('previewResult');
  const downloadLink = document.getElementById('downloadLink');

  if (processBtn) {
    processBtn.addEventListener('click', async () => {
      if (!imageFileInput.files || !imageFileInput.files[0]) {
        alert('Please select an image to process.');
        return;
      }
      const file = imageFileInput.files[0];
      const engine = engineSelect ? engineSelect.value : 'imgly_free';

      processingStatus.textContent = 'Processing...';
      downloadLink.style.display = 'none';
      previewResult.innerHTML = '';

      if (engine === 'imgly_free') {
        const dataUrl = await freeEngineProcess(file);
        const fd = new FormData();
        fd.append('action', 'upload_and_process');
        fd.append('engine', 'imgly_free');
        fd.append('data_url', dataUrl);
        fd.append('csrf_token', CSRF_TOKEN);
        const res = await postForm(fd);
        if (res.ok) {
          processingStatus.textContent = 'Done (Browser engine).';
          showResultImage(dataUrl);
          if (res.result_path) {
            downloadLink.href = toClientPath(res.result_path);
            downloadLink.style.display = 'inline-block';
          }
        } else {
          processingStatus.textContent = 'Error: ' + (res.error || 'unknown');
        }
      }
    });
  }

  function showResultImage(urlOrData) {
    previewResult.innerHTML = '';
    const img = document.createElement('img');
    img.style.maxWidth = '320px';
    img.style.borderRadius = '12px';
    img.src = urlOrData;
    previewResult.appendChild(img);
  }

  function freeEngineProcess(file) {
    return new Promise((resolve, reject) => {
      const img = new Image();
      const fr = new FileReader();
      fr.onload = () => {
        img.src = fr.result;
      };
      fr.onerror = (e) => reject(e);
      img.onload = () => {
        const canvas = document.createElement('canvas');
        canvas.width = img.naturalWidth;
        canvas.height = img.naturalHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);
        try {
          const imgd = ctx.getImageData(0, 0, canvas.width, canvas.height);
          const data = imgd.data;
          const threshold = 240;
          for (let i = 0; i < data.length; i += 4) {
            const r = data[i], g = data[i+1], b = data[i+2];
            if (r > threshold && g > threshold && b > threshold) {
              data[i+3] = 0;
            }
          }
          ctx.putImageData(imgd, 0, 0);
          const dataUrl = canvas.toDataURL('image/png');
          resolve(dataUrl);
        } catch (err) {
          resolve(fr.result);
        }
      };
      img.onerror = (e) => reject(e);
      fr.readAsDataURL(file);
    });
  }

  function toClientPath(absPath) {
    const idx = absPath.indexOf('/uploads/');
    if (idx !== -1) {
      return document.location.origin + absPath.substring(idx);
    }
    return absPath;
  }
});
