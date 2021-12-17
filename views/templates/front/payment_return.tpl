{extends file=$layout}

{block name="content"}
 
  <section>
     {if $paymentSuccess}
     <p>{l s='Féliciation votre paiement a bien été validé.'}</p>
    <p>{l s="Nous vous remercions pour la confiance et vous invitons à consulter les details de votre commande dans votre compte."}</p>
    {else}

     <p>{l s='Désolé, votre paiement a été refusé'}</p>
    <p>{l s="Veuillez contacter votre institution de paiement puis recommencer."}</p>
      
    {/if}
  </section>
{/block}
