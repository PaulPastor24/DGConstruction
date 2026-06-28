'use client'

import { makeStyles } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    marginBottom: '40px',
  },
  breadcrumb: {
    fontSize: '14px',
    color: '#999999',
    fontWeight: 500,
    marginBottom: '16px',
  },
  title: {
    fontSize: '36px',
    fontWeight: 700,
    color: '#2F6B3C',
    marginBottom: '8px',
    letterSpacing: '-0.5px',
  },
  subtitle: {
    fontSize: '15px',
    color: '#666666',
    fontWeight: 400,
    lineHeight: '1.5',
  },
})

export function TimelineHeader() {
  const styles = useStyles()

  return (
    <div className={styles.root}>
      <div className={styles.breadcrumb}>Dashboard / Project Timeline</div>
      <h1 className={styles.title}>Project Timeline</h1>
      <p className={styles.subtitle}>Monitor construction progress, phases, and project milestones.</p>
    </div>
  )
}
