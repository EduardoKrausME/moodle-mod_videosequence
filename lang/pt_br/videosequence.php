<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * videosequence.php
 *
 * @package   mod_videosequence
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['addstep'] = 'Adicionar etapa';
$string['allowseek'] = 'Permitir avançar para partes ainda não assistidas';
$string['answerunlocksat'] = 'A sequência é liberada ao atingir {$a}% assistido.';
$string['attemptlimitreached'] = 'Você atingiu o número máximo de tentativas.';
$string['attemptresult'] = 'Tentativa {$a->attempt}: {$a->correct} de {$a->total} posições corretas ({$a->score}%).';
$string['attempts'] = 'Tentativas';
$string['attemptsavedfeedbackhidden'] = 'Tentativa {$a} salva. O feedback detalhado será mostrado na última tentativa.';
$string['attemptstatus'] = 'Tentativas realizadas: {$a}';
$string['backtoactivity'] = 'Voltar para a atividade';
$string['completed'] = 'Concluída';
$string['completiondetail:submit'] = 'Enviar pelo menos uma tentativa de sequência';
$string['completiondetail:watch'] = 'Assistir pelo menos {$a}% do vídeo';
$string['completionsubmit'] = 'O aluno deve enviar pelo menos uma sequência';
$string['completionwatch'] = 'O aluno deve atingir o percentual mínimo assistido';
$string['confirmdelete'] = 'Excluir esta etapa?';
$string['correctsteps'] = 'Etapas corretas';
$string['createinstructions'] = 'Digite cada etapa na ordem demonstrada no vídeo.';
$string['editstep'] = 'Editar etapa';
$string['errornonnegative'] = 'Informe zero ou um número positivo.';
$string['errorpercent'] = 'Informe um percentual entre 0 e 100.';
$string['errortimeend'] = 'O tempo final deve ser posterior ao inicial.';
$string['errorvideourl'] = 'Informe a URL do vídeo para esta fonte.';
$string['feedbackfinal'] = 'Mostrar somente na última tentativa permitida';
$string['feedbackimmediate'] = 'Mostrar após cada tentativa';
$string['feedbackmode'] = 'Feedback';
$string['fillallsteps'] = 'Preencha todas as etapas antes de enviar.';
$string['incomplete'] = 'Incompleta';
$string['incompleteanswer'] = 'Sequência incompleta';
$string['invalidanswer'] = 'A sequência enviada é inválida.';
$string['managesequence'] = 'Gerenciar sequência';
$string['maxattempts'] = 'Máximo de tentativas';
$string['maxattempts_help'] = 'Use 0 para tentativas ilimitadas.';
$string['minwatchpercent'] = 'Percentual mínimo assistido antes de responder';
$string['modecreate'] = 'Criar a sequência de memória';
$string['modereorder'] = 'Reordenar as etapas fornecidas';
$string['modulename'] = 'Sequência em Vídeo';
$string['modulenameplural'] = 'Sequências em Vídeo';
$string['noreportdata'] = 'Nenhum estudante matriculado foi encontrado.';
$string['nosteps'] = 'Nenhuma etapa foi configurada.';
$string['nostepsstudent'] = 'O professor ainda não configurou as etapas da sequência.';
$string['notenoughwatched'] = 'Você precisa assistir pelo menos {$a}% do vídeo antes de enviar.';
$string['pluginadministration'] = 'Administração do Video Sequence';
$string['pluginname'] = 'Sequência em Vídeo';
$string['poster'] = 'Imagem de capa';
$string['privacy:metadata:attempts'] = 'Armazena as tentativas de sequência enviadas pelos estudantes.';
$string['privacy:metadata:attempts:answer'] = 'Ordem das etapas ou sequência digitada.';
$string['privacy:metadata:attempts:grade'] = 'Nota calculada da tentativa.';
$string['privacy:metadata:attempts:score'] = 'Percentual calculado da tentativa.';
$string['privacy:metadata:attempts:userid'] = 'Usuário que enviou a tentativa.';
$string['privacy:metadata:external:videourl'] = 'A URL pública configurada pode ser enviada ao provedor externo de vídeo.';
$string['privacy:metadata:progress'] = 'Armazena o progresso assistido de cada estudante.';
$string['privacy:metadata:progress:lastposition'] = 'Última posição assistida no vídeo.';
$string['privacy:metadata:progress:percent'] = 'Percentual de conteúdo único do vídeo assistido.';
$string['privacy:metadata:progress:userid'] = 'Usuário cujo progresso é armazenado.';
$string['privacy:metadata:progress:watchedsegments'] = 'Intervalos únicos do vídeo assistidos pelo usuário.';
$string['privacy:metadata:vimeo'] = 'O Vimeo pode receber dados normais de requisição do player quando essa fonte é usada.';
$string['privacy:metadata:youtube'] = 'O YouTube pode receber dados normais de requisição do player quando essa fonte é usada.';
$string['remainingattempts'] = 'Tentativas restantes: {$a}';
$string['reorderinstructions'] = 'Arraste as etapas para a ordem correta ou use os botões de seta.';
$string['report'] = 'Relatório';
$string['resetuserdata'] = 'Excluir progresso e tentativas da Sequência em Vídeo';
$string['resumeplayback'] = 'Retomar da última posição assistida';
$string['reviewclip'] = 'Rever trecho';
$string['savestep'] = 'Salvar etapa';
$string['sequenceheader'] = 'Configurações da sequência';
$string['sequencemode'] = 'Modo de resposta do aluno';
$string['sequencemode_help'] = 'No modo de reordenação, as etapas configuradas aparecem embaralhadas. No modo de criação, os nomes ficam ocultos e o aluno digita a sequência.';
$string['sourceupload'] = 'Vídeo enviado';
$string['sourceurl'] = 'URL direta do vídeo';
$string['sourcevimeo'] = 'Vimeo';
$string['sourceyoutube'] = 'YouTube';
$string['status'] = 'Situação';
$string['stepaliases'] = 'Nomes alternativos aceitos';
$string['stepaliases_help'] = 'Um nome por linha ou separado por ponto e vírgula. Usado somente no modo de criação.';
$string['stepdescription'] = 'Descrição';
$string['steptitle'] = 'Nome da etapa';
$string['student'] = 'Estudante';
$string['submitsequence'] = 'Enviar sequência';
$string['submittedsequence'] = 'Sequência enviada';
$string['timeend'] = 'Fim no vídeo (segundos, opcional)';
$string['timestart'] = 'Início no vídeo (segundos)';
$string['videofile'] = 'Arquivo de vídeo';
$string['videoheader'] = 'Vídeo';
$string['videosequence:addinstance'] = 'Adicionar uma atividade Sequência em Vídeo';
$string['videosequence:manage'] = 'Gerenciar etapas da sequência';
$string['videosequence:view'] = 'Visualizar atividades Sequência em Vídeo';
$string['videosequence:viewreports'] = 'Visualizar relatórios da Sequência em Vídeo';
$string['videosequencename'] = 'Nome da Sequência em Vídeo';
$string['videosource'] = 'Fonte do vídeo';
$string['videotime'] = 'Momento do vídeo';
$string['videourl'] = 'URL do vídeo';
$string['watchbeforeanswer'] = 'Assista pelo menos {$a}% do vídeo antes de responder.';
$string['watched'] = 'Assistido';
$string['yoursequence'] = 'Sua sequência';
