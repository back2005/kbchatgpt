{*
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 *}
 <div id="chatgpt-api-setup-panel" class="panel">
 <h3>{l s='ChatGPT API Key Setup Instructions' mod='kbchatgpt'}</h3>
    
 <p>{l s='To use ChatGPT features, you need to set up an API Key. Please follow these steps:' mod='kbchatgpt'}</p>
 
 <ol>
     <li>
         <strong>{l s='Obtain an API Key' mod='kbchatgpt'}:</strong>
         <p>{l s='Go to the OpenAI API Key generation page at' mod='kbchatgpt'} <a href="https://platform.openai.com/account/api-keys" target="_blank">https://platform.openai.com/account/api-keys</a> {l s='and log in or create an account if you haven\'t already.' mod='kbchatgpt'}</p>
         <p>{l s='Click "Create new secret key" to generate an API Key. Copy this key for the next step.' mod='kbchatgpt'}</p>
     </li>
     <li>
         <strong>{l s='Enter the API Key in Your PrestaShop Module' mod='kbchatgpt'}:</strong>
         <p>{l s='In your PrestaShop Back Office, go to the "Knowband Prestashop ChatGPT Generator/Translator" General Settings page.' mod='kbchatgpt'}</p>
         <p>{l s='Paste the API Key into the provided "API Key for ChatGPT" field.' mod='kbchatgpt'}</p>
     </li>
     <li>
         <strong>{l s='Save the Settings' mod='kbchatgpt'}:</strong>
         <p>{l s='After entering the API Key, click "Save" to store your settings. The ChatGPT module will now use this key to interact with the OpenAI API.' mod='kbchatgpt'}</p>
     </li>
 </ol>

 
 <div class="alert alert-warning">
        <strong>{l s='Billing Requirement:' mod='kbchatgpt'}</strong> 
        {l s='Ensure that you have a sufficient credit balance in your OpenAI account for the API to function. If your credit balance is depleted, the API requests will not work.' mod='kbchatgpt'}
        {l s='You can check your balance and add funds by visiting the billing overview page at' mod='kbchatgpt'} <a href="https://platform.openai.com/settings/organization/billing/overview" target="_blank">https://platform.openai.com/settings/organization/billing/overview</a>.
    </div>
    <div class="alert alert-info">
     <strong>{l s='Note:' mod='kbchatgpt'}</strong> {l s='Keep your API Key secure and do not share it publicly, as it is linked to your OpenAI account usage and billing.' mod='kbchatgpt'}
 </div>
</div>

<style>
/* Style for the API Key setup panel */
#chatgpt-api-setup-panel {
    padding: 20px;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 20px;
}

#chatgpt-api-setup-panel h3 {
    font-size: 1.5em;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
}

#chatgpt-api-setup-panel p {
    font-size: 1em;
    color: #555;
    line-height: 1.5em;
}

#chatgpt-api-setup-panel ol {
    padding-left: 20px;
}

#chatgpt-api-setup-panel ol li {
    margin-bottom: 15px;
}

#chatgpt-api-setup-panel ol li strong {
    color: #333;
    font-weight: bold;
}

#chatgpt-api-setup-panel a {
    color: #007bff;
    text-decoration: underline;
}

#chatgpt-api-setup-panel a:hover {
    color: #0056b3;
    text-decoration: none;
}

</style>