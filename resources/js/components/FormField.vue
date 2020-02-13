<template>

    <div class="nested-form-container">
        <template v-if="field.children.length > 0">
            <!-- ACTUAL FIELDS -->
            <nested-form-field v-for="(child, index) in field.children"
                               :key="`${field.attribute}-${index}`"
                               :index="index"
                               :field="field"
                               :child="child"
                               :errors="errors"/>
            <!-- ACTUAL FIELDS -->
        </template>

        <template v-else>
            <p class="m-8 text-center">{{__('No :resourcePluralName', { resourcePluralName: field.pluralLabel })}}.</p>
        </template>

    </div>

</template>

<script>
    import { FormField, HandlesValidationErrors } from "laravel-nova"

    import NestedFormField from "./NestedFormField"


    export default {
        mixins: [FormField, HandlesValidationErrors],

        props: ["resourceName", "resourceId", "field"],

        components: { NestedFormField },

        methods: {
            /**
             * This adds a resource to the children
             */
            add() {
                if(this.field.children.length  <= 0)
                for(let i =0;i<this.field.max;i++)
                this.field.children.push(this.replaceIndexesInSchema(this.field))
            },

            /**
             * Overrides the fill method.
             */
            fill(formData) {
                this.field.children.forEach(child => child.fill(formData))

            },

            /**
             * This replaces the "{{index}}" values of the schema to
             * their actual index.
             *
             */
            replaceIndexesInSchema(field) {

                const schema = JSON.parse(JSON.stringify(field.schema));


                schema.fields.forEach(field => {
                    if (field.schema) {
                        field.schema.opened = false;

                        field.schema = this.replaceIndexesInSchema(field)

                    }

                    if (field.attribute) {
                        field.attribute = field.attribute.replace(
                            this.field.INDEX,
                            this.field.children.length
                        )

                    }
                });


                schema.heading = schema.heading.replace(
                    this.field.INDEX,
                    this.field.children.length + 1
                );

                schema.attribute = schema.attribute.replace(
                    this.field.INDEX,
                    this.field.children.length
                );


                return schema

            }
        },
        mounted() {
            // Appends the required amount of children to the form
            this.add()
        }
    }

</script>
