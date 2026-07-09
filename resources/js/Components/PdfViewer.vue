<template>
  <div class="pdf-viewer-root" style="height: 100%; overflow-y: auto; background: #e5e7eb;">
    <div v-if="loading" style="display:flex;align-items:center;justify-content:center;padding:2rem;">
      <span style="font-size:12px;color:#9ca3af;">Memuat PDF...</span>
    </div>

    <div v-if="error" style="display:flex;align-items:center;justify-content:center;padding:2rem;">
      <span style="font-size:12px;color:#ef4444;">{{ error }}</span>
    </div>

    <div v-for="page in pages" :key="page.num"
      style="position:relative;margin:0 auto 8px;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.15);"
      :style="{ width: page.w + 'px', height: page.h + 'px' }">
      <canvas :ref="el => setRef(canvasMap, page.num, el)"
        style="display:block;"/>
      <div :ref="el => setRef(textMap, page.num, el)"
        style="position:absolute;inset:0;overflow:hidden;line-height:1;pointer-events:none;"/>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onUnmounted, nextTick } from 'vue'
import * as pdfjsLib from 'pdfjs-dist'
import { TextLayer } from 'pdfjs-dist'
import workerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url'

pdfjsLib.GlobalWorkerOptions.workerSrc = workerUrl

const props = defineProps({
  src:        { type: String, default: null },
  highlights: { type: Array,  default: () => [] },
  scale:      { type: Number, default: 1.2 },
})

const loading = ref(false)
const error   = ref(null)
const pages   = ref([])

const canvasMap = {}
const textMap   = {}
let pdfDoc      = null
let renderTasks = []
let tlMap       = {}

function setRef(map, key, el) {
  if (el) map[key] = el
  else delete map[key]
}

async function load() {
  if (!props.src) {
    pages.value = []
    return
  }

  loading.value = true
  error.value   = null
  pages.value   = []

  renderTasks.forEach(t => { try { t.cancel() } catch {} })
  renderTasks = []
  Object.values(tlMap).forEach(tl => { try { tl.cancel() } catch {} })
  tlMap = {}
  if (pdfDoc) { pdfDoc.destroy(); pdfDoc = null }

  try {
    pdfDoc = await pdfjsLib.getDocument({ url: props.src, isEvalSupported: false }).promise

    // Ambil ukuran semua halaman dulu
    const pageData = []
    for (let i = 1; i <= pdfDoc.numPages; i++) {
      const pg = await pdfDoc.getPage(i)
      const vp = pg.getViewport({ scale: props.scale })
      pageData.push({ num: i, w: Math.round(vp.width), h: Math.round(vp.height) })
    }

    pages.value   = pageData
    loading.value = false

    await nextTick()

    // Render tiap halaman
    for (let i = 1; i <= pdfDoc.numPages; i++) {
      await renderPage(i)
    }
  } catch (e) {
    console.error('[PdfViewer]', e)
    error.value   = 'Gagal memuat PDF: ' + (e?.message ?? e)
    loading.value = false
  }
}

async function renderPage(n) {
  const canvas = canvasMap[n]
  const tlDiv  = textMap[n]
  if (!canvas) return

  const page     = await pdfDoc.getPage(n)
  const viewport = page.getViewport({ scale: props.scale })

  canvas.width  = viewport.width
  canvas.height = viewport.height

  const task = page.render({ canvasContext: canvas.getContext('2d'), viewport })
  renderTasks.push(task)
  try {
    await task.promise
  } catch (e) {
    if (e?.name !== 'RenderingCancelledException') console.warn('[PdfViewer] render', n, e)
    return
  }

  if (!tlDiv) return

  tlDiv.innerHTML  = ''

  try {
    const content = await page.getTextContent()
    const tl = new TextLayer({ textContentSource: content, container: tlDiv, viewport })
    tlMap[n] = tl
    await tl.render()
    applyHighlights(tl.textContentItemsStr, tl.textDivs)
  } catch (e) {
    console.warn('[PdfViewer] textlayer', n, e)
  }
}

function applyHighlights(strs, divs) {
  if (!props.highlights?.length || !divs?.length) return
  const needles = props.highlights
    .filter(h => h && h.length > 4)
    .map(h => h.toLowerCase().replace(/\s+/g, ' ').trim())

  divs.forEach((div, i) => {
    const txt = (strs[i] ?? '').toLowerCase().replace(/\s+/g, ' ').trim()
    if (txt.length < 3) return
    for (const needle of needles) {
      if (txt.includes(needle) || needle.includes(txt)) {
        div.style.backgroundColor = 'rgba(253,224,71,0.8)'
        div.style.borderRadius    = '2px'
        break
      }
    }
  })
}

watch(() => props.src, load, { immediate: true })

watch(() => props.highlights, () => {
  if (!pdfDoc) return
  Object.entries(tlMap).forEach(([, tl]) => {
    applyHighlights(tl.textContentItemsStr, tl.textDivs)
  })
}, { deep: true })

onUnmounted(() => {
  renderTasks.forEach(t => { try { t.cancel() } catch {} })
  Object.values(tlMap).forEach(tl => { try { tl.cancel() } catch {} })
  if (pdfDoc) pdfDoc.destroy()
})
</script>

<style>
/* Minimal text layer — span warna transparan, highlight kuning override */
.pdf-viewer-root div[style*="position:absolute"] span,
.pdf-viewer-root div[style*="position: absolute"] span {
  color: transparent !important;
  position: absolute;
  white-space: pre;
  transform-origin: 0% 0%;
  cursor: default;
}
</style>
