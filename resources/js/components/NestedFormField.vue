<template>
  <div class="nested-form">
    <div v-show="child.opened">
      <!-- ACTUAL FIELDS -->
      <component v-for="subfield in child.fields"
                 :field="subfield"
                 :key="subfield.attribute"
                 :errors="errors"
                 :resource-id="child[field.ID]"
                 :resource-name="field.resourceName"
                 :via-resource="field.viaResource"
                 :via-resource-id="field.viaResourceId"
                 :via-relationship="field.viaRelationship"
                 :related-resource-name="field.relatedResourceName"
                 :related-resource-id="field.relatedResourceId"
                 :is="`form-${getComponent(subfield)}`" />
      <!-- ACTUAL FIELDS -->
    </div>
  </div>
</template>

<script>
import FormNestedBelongsToField from './CustomNestedFields/BelongsToField'
import FormNestedFileField from './CustomNestedFields/FileField'
import DeleteModal from './Modals/Delete'

export default {

  components: { FormNestedBelongsToField, FormNestedFileField, DeleteModal },


  props: {
    field: {
      type: Object,
      required: true
    },
    child: {
      type: Object,
      required: true
    },
    index: {
      type: Number
    },
    errors: {
      type: Object
    }
  },

  computed: {

    /**
     * Get the error attribute
     */
    errorAttribute() {
      return `${this.field.attribute}${this.field.has_many ? `[${this.index}]` : ''}`
    },

    /**
     * Checks whether the field has errors
     */
    hasError() {
      return Object.keys(this.errors.errors).includes(this.errorAttribute)
    },

    /**
     * Get the first error
     */
    firstError() {
      return this.errors.errors[this.errorAttribute][0]
    }
  },

  methods: {
    /**
     * Fill the formData with the children.
     */
    fill(formData) {
      this.child.fields.forEach(field => field.fill(formData))

      if (this.child[this.field.ID]) {
        formData.append(`${this.field.attribute}[${this.index}][${this.field.ID}]`, this.child[this.field.ID])
      }
    },

    /**
     * Get the component dependind on the field.
     */
    getComponent(field) {

      if (['belongs-to-field', 'file-field'].includes(field.component)) {
        return 'nested-' + field.component
      }

      return field.component

    }
  },

  watch: {
    /**
     * Watches for errors in sub fields.
     */
    errors({ errors }) {
      for (let attribute in errors) {
        if (attribute.includes(this.field.attribute)) {
          this.child.opened = true;
          break
        }
      }
    }
  },
  created() {
    this.child.fill = this.fill
  }
}
</script>
