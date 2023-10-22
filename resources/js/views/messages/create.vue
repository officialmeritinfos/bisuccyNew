<template>
  <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
      <PageTitle :title="$t('createMessage')" />
  </div>

  <Form :validation-schema="validationSchema" @submit="handleSubmit">
      <div class="grid grid-cols-12">
          <div class="col-span-12 lg:col-span-6">
              <div class="intro-y box p-5 mt-5">
                  <CreateForm />
                  <div class="mt-6 flex justify-end">
                      <PrimaryButton type="submit" :text="$t('submit')" custom-class="w-24"/>
                  </div>
              </div>
          </div>
      </div>
  </Form>
</template>

<script setup>
import PageTitle from "@/components/core/PageTitle.vue";
import CreateForm from './forms/create.vue';
import PrimaryButton from "@/components/core/PrimaryButton.vue";
import { Form } from 'vee-validate';
import * as Yup from 'yup';
import { useRouter } from "vue-router";
import { useMessagesStore } from "@/stores/messages";

// Import the stores
const messagesStore = useMessagesStore();

const router = useRouter();

const handleSubmit = async (values) => {
  await messagesStore.createSignal(values).then(() => {
    router.push({ name: "messages" });
  })
}

const validationSchema = Yup.object().shape({
  title: Yup.string().required().nullable().label("Name"),
  content: Yup.string().required().nullable().label("Content"),
  timeToBroadcast: Yup.date().required().nullable().label("Broadcast Time"),
  type: Yup.number().required().nullable().label("Type"),
});



</script>