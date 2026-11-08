@extends('layout.app')

@section('title', 'Text to Voice')

@section('content')
<style>
body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #e3f2fd, #bbdefb);
  min-height: 100vh;
}
.voice-card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.1);
  padding: 30px;
  max-width: 700px;
  margin: 40px auto;
}
.voice-card h2 {
  font-weight: 700;
  color: #0d47a1;
}
select, textarea {
  border-radius: 10px !important;
}
button {
  border-radius: 10px !important;
  transition: 0.3s;
}
button:hover {
  opacity: 0.9;
}
.download-btn {
  background: #2e7d32;
  color: white;
  border: none;
  padding: 10px 25px;
  border-radius: 10px;
}
.download-btn:hover {
  background: #1b5e20;
}
</style>

<div class="voice-card text-center">
  <h2>🎙️ Text to Voice Converter</h2>
  <p class="text-muted mb-4">Type your text, choose a voice and language, and download your speech as MP3.</p>

  <div class="mb-3">
    <textarea id="textInput" class="form-control" rows="5" placeholder="Type or paste text here..."></textarea>
  </div>

  <div class="row mb-3">
    <div class="col-md-6">
      <label for="langSelect" class="form-label fw-bold">Language</label>
      <select id="langSelect" class="form-select">
        <option value="en-US">English</option>
        <option value="ur-PK">Urdu</option>
        <option value="hi-IN">Hindi</option>
      </select>
    </div>

    <div class="col-md-6">
      <label for="voiceSelect" class="form-label fw-bold">Voice</label>
      <select id="voiceSelect" class="form-select"></select>
    </div>
  </div>

  <div class="d-flex justify-content-center gap-3 mt-4">
    <button id="convertBtn" class="btn btn-primary px-4 py-2">🔊 Play</button>
    <button id="downloadBtn" class="download-btn px-4 py-2">💾 Download MP3</button>
  </div>

  <audio id="audioPlayer" controls class="mt-4 w-100"></audio>
</div>

<script>
const voiceSelect = document.getElementById("voiceSelect");
let voices = [];

function loadVoices() {
  voices = speechSynthesis.getVoices();

  voiceSelect.innerHTML = "";
  voices.forEach((voice, index) => {
    const option = document.createElement("option");
    option.value = index;
    option.textContent = `${voice.name} (${voice.lang}) ${voice.gender || ''}`;
    voiceSelect.appendChild(option);
  });

  if (voices.length === 0) {
    setTimeout(loadVoices, 500);
  }
}
loadVoices();
speechSynthesis.onvoiceschanged = loadVoices;

document.getElementById("convertBtn").addEventListener("click", () => {
  const text = document.getElementById("textInput").value.trim();
  const lang = document.getElementById("langSelect").value;
  const selectedVoice = voiceSelect.value;

  if (!text) return alert("Please enter text!");

  const utter = new SpeechSynthesisUtterance(text);
  utter.lang = lang;
  utter.voice = voices[selectedVoice];
  utter.rate = 1;
  utter.pitch = 1;
  speechSynthesis.cancel();
  speechSynthesis.speak(utter);
});

document.getElementById("downloadBtn").addEventListener("click", async () => {
  const text = document.getElementById("textInput").value.trim();
  const lang = document.getElementById("langSelect").value;

  if (!text) return alert("Please enter text first!");

  // Use Google Translate TTS for download link
  const url = `https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&q=${encodeURIComponent(text)}&tl=${lang}`;
  
  const a = document.createElement('a');
  a.href = url;
  a.download = "voice.mp3";
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
});
</script>
@endsection
