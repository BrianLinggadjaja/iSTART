<template>
  <div class="row">
    <div class="col-12">
      <p
        v-for="(element, index) in paragraph"
        :key="paragraph[`${index}`]"
        class="module-text__text"
        v-html="paragraph[`${index}`]">
      </p>
    </div>
  </div>
</template>

<script>
export default {
  props: [
    'paragraph'
  ]
}
</script>
